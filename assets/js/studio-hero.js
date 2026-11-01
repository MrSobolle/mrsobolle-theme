(() => {
    'use strict';
    const hero = document.querySelector('[data-studio-hero]');
    const art = hero?.querySelector('[data-studio-art]');
    if (!hero || !art) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const fine = window.matchMedia('(pointer: fine)');
    const gsap = window.gsap;
    if (gsap && !reduced.matches) {
        gsap.from(hero.querySelectorAll('.hero-section__eyebrow, .hero-section__title, .hero-section__lead, .studio-hero__credit, .hero-section__actions'), {
            y: 24, opacity: 0, stagger: 0.11, duration: 1.15, ease: 'power3.out', clearProps: 'all'
        });
    }
    if (reduced.matches || !window.WebGLRenderingContext) return;
    const moduleUrl = art.dataset.threeModule;
    if (!moduleUrl) return;
    import(moduleUrl).then((THREE) => {
        if (!art.isConnected || reduced.matches) return;
        const renderer = new THREE.WebGLRenderer({alpha: true, antialias: true, powerPreference: 'low-power'});
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.7));
        renderer.setClearColor(0x000000, 0);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.35;
        art.append(renderer.domElement);
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(34, 1, .1, 100);
        camera.position.set(0, 0, 9.6);
        const shape = new THREE.Mesh(
            new THREE.TorusKnotGeometry(1.65, .51, 220, 28, 2, 5),
            new THREE.MeshPhysicalMaterial({
                color: 0xa9b8c3, metalness: .9, roughness: .22,
                clearcoat: .85, clearcoatRoughness: .17
            })
        );
        scene.add(shape);
        scene.add(new THREE.AmbientLight(0x879cae, 1.2));
        const key = new THREE.PointLight(0xe8f5ff, 95); key.position.set(-4, 4, 5); scene.add(key);
        const rim = new THREE.PointLight(0x547aa6, 105); rim.position.set(4, -2, 2); scene.add(rim);
        const fill = new THREE.PointLight(0xc4ccd6, 55); fill.position.set(2, 4, -4); scene.add(fill);
        const target = {x: 0, y: 0};
        let visible = true, frame = 0, t0 = performance.now();
        const resize = () => {
            const w = Math.max(1, art.clientWidth), h = Math.max(1, art.clientHeight);
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h, false);
            shape.scale.setScalar(w < 520 ? .82 : 1);
        };
        const pointer = (e) => {
            if (!fine.matches) return;
            const rect = hero.getBoundingClientRect();
            target.y = ((e.clientX - rect.left) / rect.width - .5) * .75;
            target.x = ((e.clientY - rect.top) / rect.height - .5) * .48;
        };
        hero.addEventListener('pointermove', pointer, {passive: true});
        hero.addEventListener('pointerleave', () => {target.x = 0; target.y = 0;});
        const observer = new IntersectionObserver(entries => {visible = entries[0].isIntersecting;});
        observer.observe(hero);
        const ro = new ResizeObserver(resize); ro.observe(art); resize();
        art.classList.add('has-webgl');
        const tick = (now) => {
            if (!art.isConnected || reduced.matches) {
                renderer.dispose(); shape.geometry.dispose(); shape.material.dispose();
                observer.disconnect(); ro.disconnect(); art.classList.remove('has-webgl');
                renderer.domElement.remove(); return;
            }
            if (visible && !document.hidden) {
                const elapsed = (now - t0) * .001;
                shape.rotation.x += (target.x + Math.sin(elapsed * .22) * .09 - shape.rotation.x) * .035;
                shape.rotation.y += (target.y + elapsed * .12 - shape.rotation.y) * .018;
                shape.rotation.z = Math.sin(elapsed * .17) * .09;
                renderer.render(scene, camera);
            }
            frame = requestAnimationFrame(tick);
        };
        frame = requestAnimationFrame(tick);
    }).catch(() => {
        /* Static metallic fallback remains visible if WebGL/modules fail. */
    });
})();
