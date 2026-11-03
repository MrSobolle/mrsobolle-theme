/**
 * Sobolle Studio interactive hover + responsive kinetic ribbon.
 * Uses local GSAP and lazy-loaded Three.js on the front page.
 */
(() => {
    'use strict';
    const hero = document.querySelector('[data-studio-hero]');
    const art = hero?.querySelector('[data-studio-art]');
    if (!hero || !art) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const fine = window.matchMedia('(pointer: fine)');
    const gsap = window.gsap;

    // Pointer-sensitive blurred glow lives behind the actual link text.
    document.querySelectorAll('.site-header .main-navigation a, .site-header .language-switcher__link, .hero-section__actions .button')
        .forEach((link) => {
            link.addEventListener('pointermove', (event) => {
                if (!fine.matches || reduced.matches) return;
                const bounds = link.getBoundingClientRect();
                if (!bounds.width || !bounds.height) return;
                link.style.setProperty('--studio-pointer-x', Math.round(((event.clientX - bounds.left) / bounds.width) * 100) + '%');
                link.style.setProperty('--studio-pointer-y', Math.round(((event.clientY - bounds.top) / bounds.height) * 100) + '%');
            }, {passive: true});
            link.addEventListener('pointerleave', () => {
                link.style.removeProperty('--studio-pointer-x');
                link.style.removeProperty('--studio-pointer-y');
            });
        });

    if (gsap && !reduced.matches) {
        gsap.from(hero.querySelectorAll('.studio-hero__wordmark, .studio-hero__studio, .hero-section__lead, .hero-section__actions'), {
            y: 16, opacity: 0, stagger: 0.1, duration: 1.05,
            ease: 'power3.out', clearProps: 'all'
        });
    }
    if (reduced.matches || !window.WebGLRenderingContext || !art.dataset.threeModule) return;

    import(art.dataset.threeModule).then((THREE) => {
        if (!art.isConnected || reduced.matches) return;

        const renderer = new THREE.WebGLRenderer({alpha: true, antialias: true, powerPreference: 'low-power'});
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
        renderer.setClearColor(0x000000, 0);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.15;

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 100);
        camera.position.set(0, 0, 10.5);
        const sculpture = new THREE.Group();

        // Two-dimensional Möbius strip with a modest z bend.
        const sections = 240;
        const widthSections = 18;
        const positions = [], colors = [], triangles = [];
        const primary = new THREE.Color(0x58c4f7);
        const secondary = new THREE.Color(0x7966fb);

        const ribbonPoint = (angle, v) => {
            const twist = Math.cos(angle * 0.5);
            const r = 2.32 + v * twist;
            return new THREE.Vector3(
                r * Math.cos(angle),
                r * Math.sin(angle),
                v * Math.sin(angle * 0.5) + .19 * Math.sin(angle * 2)
            );
        };

        for (let i = 0; i <= sections; i++) {
            const a = (i / sections) * Math.PI * 2;
            for (let j = 0; j <= widthSections; j++) {
                const v = (j / widthSections - .5) * 1.95;
                const p = ribbonPoint(a, v);
                positions.push(p.x, p.y, p.z);
                const mix = Math.min(1, Math.max(0, .46 + Math.sin(a * 1.15 + v * 1.4) * .34));
                const color = primary.clone().lerp(secondary, mix);
                colors.push(color.r, color.g, color.b);
                if (i < sections && j < widthSections) {
                    const v0 = i * (widthSections + 1) + j;
                    const v1 = v0 + widthSections + 1;
                    triangles.push(v0, v1, v0 + 1, v1, v1 + 1, v0 + 1);
                }
            }
        }
        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
        geometry.setIndex(triangles);
        geometry.computeVertexNormals();

        const material = new THREE.MeshPhysicalMaterial({
            side: THREE.DoubleSide, vertexColors: true,
            metalness: .56, roughness: .28, clearcoat: .85,
            clearcoatRoughness: .2, transparent: true,
            opacity: .84, depthWrite: false
        });
        sculpture.add(new THREE.Mesh(geometry, material));

        const contourGeometries = [], contourMaterials = [];
        for (let c = 0; c <= 12; c++) {
            const v = (c / 12 - .5) * 1.95;
            const coords = [];
            for (let n = 0; n <= sections; n++) {
                const p = ribbonPoint(n / sections * Math.PI * 2, v);
                coords.push(p.x, p.y, p.z);
            }
            const lineGeometry = new THREE.BufferGeometry();
            lineGeometry.setAttribute('position', new THREE.Float32BufferAttribute(coords, 3));
            const lineMaterial = new THREE.LineBasicMaterial({
                color: c % 3 === 0 ? 0x9acfff : 0x8d89ff,
                transparent: true, opacity: c === 0 || c === 12 ? .32 : .14,
                depthWrite: false
            });
            sculpture.add(new THREE.Line(lineGeometry, lineMaterial));
            contourGeometries.push(lineGeometry);
            contourMaterials.push(lineMaterial);
        }
        scene.add(sculpture);
        scene.add(new THREE.AmbientLight(0xa0bbff, .9));
        const blue = new THREE.PointLight(0x68caff, 95);
        blue.position.set(-3.8, 2.6, 4.8); scene.add(blue);
        const violet = new THREE.PointLight(0x7559ec, 90);
        violet.position.set(3, -2.7, 3.9); scene.add(violet);
        const rim = new THREE.PointLight(0xc8deff, 65);
        rim.position.set(1.9, 1.7, -3.9); scene.add(rim);

        const target = {x: 0, y: 0};
        let visible = true;
        const started = performance.now();
        let disposed = false;
        const resize = () => {
            const w = Math.max(1, art.clientWidth), h = Math.max(1, art.clientHeight);
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h, false);
            sculpture.scale.setScalar(w < 580 ? .87 : 1);
        };
        const move = (event) => {
            if (!fine.matches) return;
            const rect = hero.getBoundingClientRect();
            target.y = ((event.clientX - rect.left) / rect.width - .5) * .56;
            target.x = ((event.clientY - rect.top) / rect.height - .5) * .39;
        };
        const leave = () => {target.x = 0; target.y = 0;};
        hero.addEventListener('pointermove', move, {passive: true});
        hero.addEventListener('pointerleave', leave);
        const intersection = new IntersectionObserver(entries => {visible = entries[0].isIntersecting;});
        intersection.observe(hero);
        const observer = new ResizeObserver(resize);
        observer.observe(art);
        resize();
        art.append(renderer.domElement);
        art.classList.add('has-webgl');

        const dispose = () => {
            if (disposed) return;
            disposed = true;
            hero.removeEventListener('pointermove', move);
            hero.removeEventListener('pointerleave', leave);
            intersection.disconnect(); observer.disconnect();
            geometry.dispose(); material.dispose();
            contourGeometries.forEach(g => g.dispose());
            contourMaterials.forEach(m => m.dispose());
            renderer.dispose();
            renderer.domElement.remove();
            art.classList.remove('has-webgl');
        };
        const draw = (now) => {
            if (!art.isConnected || reduced.matches) {dispose(); return;}
            if (visible && !document.hidden) {
                const t = (now - started) * .001;
                const x = -.37 + Math.sin(t * .21) * .11 + target.x;
                const y = -.31 + Math.cos(t * .17) * .13 + target.y;
                const z = -.27 + Math.sin(t * .15) * .06;
                sculpture.rotation.x += (x - sculpture.rotation.x) * .04;
                sculpture.rotation.y += (y - sculpture.rotation.y) * .04;
                sculpture.rotation.z += (z - sculpture.rotation.z) * .04;
                renderer.render(scene, camera);
            }
            requestAnimationFrame(draw);
        };
        requestAnimationFrame(draw);
    }).catch(() => {
        // CSS fallback intentionally stays visible if import or WebGL fails.
    });
})();