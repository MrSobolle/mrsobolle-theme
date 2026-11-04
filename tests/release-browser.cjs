/* Read-only release E2E. Node >=20; Playwright; isolated browser contexts. */
const fs=require('fs'),path=require('path');
const {chromium}=require('playwright');
const [baseArg,inventoryPath,outDir,mode='desktop']=process.argv.slice(2);
const base=(baseArg||'').replace(/\/$/,'');
if(!['http://127.0.0.1:18866','https://mrsobolle.com'].includes(base))throw Error('Unexpected target');
const inventory=JSON.parse(fs.readFileSync(inventoryPath,'utf8').replace(/^\uFEFF/,''));
fs.mkdirSync(outDir,{recursive:true});
const viewport=mode==='mobile'?{width:390,height:844}:mode==='narrow'?{width:320,height:740}:{width:1440,height:1000};
const cases=new Map();
function add(url,lang,status=200){cases.set(new URL(url,base).pathname,{path:new URL(url,base).pathname,lang,status});}
for(const lang of ['en','ru','de','fr','es']){
 add(lang==='en'?'/':'/'+lang+'/',lang);
 for(const template of ['page-templates/contact.php','page-templates/apps-catalog.php','page-templates/journal-catalog.php']){
  const row=inventory.find(x=>x.lang===lang&&x.template===template);if(row)add(row.url,lang);
 }
}
for(const row of inventory.filter(x=>['en','ru'].includes(x.lang)&&(x.type==='post'||x.product_key==='widecrop'||x.product_key==='ai-pc-assistant'||x.template==='page-templates/work-catalog.php'||(x.slug==='docs'&&x.parent===9))))add(row.url,row.lang);
add('/this-page-does-not-exist-qa/','en',404);
const report={base,mode,viewport,checks:0,routes:[],failures:[],interactions:[]};
function check(ok,message,context){report.checks++;if(!ok)report.failures.push({context,message});}
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.QA_CHROME_EXECUTABLE||'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'});
 try{
  const context=await browser.newContext({viewport,deviceScaleFactor:1,isMobile:mode!=='desktop',hasTouch:mode!=='desktop',reducedMotion:'reduce'});
  await context.addInitScript(()=>{window.__metrics={cls:0,lcp:0};try{new PerformanceObserver(l=>{for(const e of l.getEntries())if(!e.hadRecentInput)window.__metrics.cls+=e.value;}).observe({type:'layout-shift',buffered:true});new PerformanceObserver(l=>{const es=l.getEntries();window.__metrics.lcp=es[es.length-1].startTime;}).observe({type:'largest-contentful-paint',buffered:true});}catch{}});
  const page=await context.newPage();page.setDefaultTimeout(7000);
  let exceptions=[];page.on('pageerror',e=>exceptions.push(e.message));
  for(const item of cases.values()){
   exceptions=[];
   try{
    const response=await page.goto(base+item.path,{waitUntil:'networkidle',timeout:20000});
    await page.evaluate(()=>document.fonts.ready);
    const data=await page.evaluate(()=>({h1:document.querySelectorAll('h1').length,lang:document.documentElement.lang,width:innerWidth,scrollWidth:document.documentElement.scrollWidth,main:!!document.querySelector('main#primary'),header:!!document.querySelector('#masthead[data-studio-glass]'),blur:getComputedStyle(document.querySelector('#masthead')).backdropFilter,emptyLinks:document.querySelectorAll('a[href="#"]').length,brokenImages:[...document.images].filter(x=>!x.complete||x.naturalWidth===0).length,unlabelledImages:[...document.images].filter(x=>!x.hasAttribute('alt')).length,metrics:window.__metrics,resources:performance.getEntriesByType('resource').map(x=>({name:x.name,bytes:x.transferSize,ms:x.duration})),title:document.title}));
    check(response.status()===item.status,'HTTP '+response.status(),item.path);
    check(data.h1===1,'Expected one H1',item.path);check(data.lang.startsWith(item.lang),'Incorrect locale '+data.lang,item.path);
    check(data.main&&data.header,'Missing main/glass header',item.path);check(data.blur!=='none','Glass style missing',item.path);
    check(data.scrollWidth<=data.width+2,'Horizontal overflow '+data.scrollWidth+'/'+data.width,item.path);
    check(data.emptyLinks===0,'Dummy anchors',item.path);check(data.brokenImages===0,'Broken images',item.path);check(data.unlabelledImages===0,'Missing image alt',item.path);
    check(exceptions.length===0,'JS errors '+exceptions.join(';'),item.path);
    check((response.headers()['x-robots-tag']||'').includes('noindex'),'Noindex response header missing',item.path);
    report.routes.push({path:item.path,status:response.status(),...data,exceptions:[...exceptions]});
    if(['/', '/ru/', '/contact/', '/apps/widecrop/'].includes(item.path))await page.screenshot({path:path.join(outDir,mode+'-'+(item.path==='/'?'home':item.path.replaceAll('/','_'))+'.png'),fullPage:true});
   }catch(e){report.failures.push({context:item.path,message:String(e)});}
  }
  await page.goto(base+'/',{waitUntil:'networkidle'});await page.keyboard.press('Tab');
  check(await page.locator('a.skip-link').evaluate(el=>el===document.activeElement),'Keyboard skip link not first','keyboard');await page.keyboard.press('Enter');
  check(await page.locator('#primary').evaluate(el=>el===document.activeElement),'Skip link does not focus main','keyboard');
  const contact=inventory.find(x=>x.lang==='en'&&x.template==='page-templates/contact.php');
  if(contact){
   await page.goto(base+new URL(contact.url).pathname,{waitUntil:'networkidle'});
   const transmissions=[];const listener=req=>{if(['fetch','xhr'].includes(req.resourceType())||req.method()!=='GET')transmissions.push(req.url());};page.on('request',listener);
   await page.locator('#contact-name').fill('QA preview');await page.locator('#contact-email').fill('qa@example.invalid');await page.locator('#contact-message').fill('This is a local-only QA preview; do not deliver.');await page.locator('[data-demo-submit]').click();
   check(await page.locator('[data-demo-result]').isVisible(),'Demo result missing','contact');check(transmissions.length===0,'Demo unexpectedly transmits data','contact');
   await page.locator('button[type="reset"]').click();check(await page.locator('#contact-name').inputValue()==='','Demo reset failed','contact');check(await page.locator('[data-demo-result]').isHidden(),'Demo notice not reset','contact');page.off('request',listener);
   report.interactions.push({name:'contact-demo',transmissions});
   const nojs=await browser.newContext({viewport,javaScriptEnabled:false});const p=await nojs.newPage();await p.goto(base+new URL(contact.url).pathname);
   check(await p.locator('[data-demo-submit]').isDisabled(),'No-JS demo must not submit','no-js');check(await p.locator('a[href^="mailto:"]').count()>0,'No-JS email fallback missing','no-js');await nojs.close();
  }
  await context.close();
 }finally{await browser.close();}
 fs.writeFileSync(path.join(outDir,mode+'-browser-qa.json'),JSON.stringify(report,null,2));
 console.log(JSON.stringify({base,mode,routes:report.routes.length,checks:report.checks,failures:report.failures},null,2));process.exitCode=report.failures.length?1:0;
})().catch(e=>{console.error(e);process.exitCode=1;});
