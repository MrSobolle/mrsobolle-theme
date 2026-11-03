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
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.4));
        renderer.setClearColor(0x000000, 0);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.05;

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 100);
        camera.position.set(0, 0, 11.3);
        // Studio-softbox reflection map: generated in memory, no external HDRI.
        const lightMap = document.createElement('canvas');
        lightMap.width = 512;
        lightMap.height = 256;
        const lightContext = lightMap.getContext('2d');
        lightContext.fillStyle = '#090f24';
        lightContext.fillRect(0, 0, lightMap.width, lightMap.height);
        [
            [40, 32, 32, 188, '#a5dfff'],
            [155, 45, 14, 171, '#628ad9'],
            [275, 64, 35, 124, '#c0a8f4'],
            [420, 27, 23, 199, '#dbedff']
        ].forEach(([x, y, w, h, color]) => {
            const gradient = lightContext.createLinearGradient(x - w, 0, x + w * 2, 0);
            gradient.addColorStop(0, '#090f24');
            gradient.addColorStop(.5, color);
            gradient.addColorStop(1, '#090f24');
            lightContext.fillStyle = gradient;
            lightContext.fillRect(x - w, y, w * 3, h);
        });
        const lightTexture = new THREE.CanvasTexture(lightMap);
        lightTexture.mapping = THREE.EquirectangularReflectionMapping;
        lightTexture.colorSpace = THREE.SRGBColorSpace;
        const pmrem = new THREE.PMREMGenerator(renderer);
        const environmentTarget = pmrem.fromEquirectangular(lightTexture);
        scene.environment = environmentTarget.texture;
        lightTexture.dispose();
        pmrem.dispose();
        const sculpture = new THREE.Group();

        // Two-dimensional Möbius strip with a modest z bend.
        const sections = 240;
        const widthSections = 16;
        const positions = [], colors = [], triangles = [];
        const primary = new THREE.Color(0x96d9f9);
        const secondary = new THREE.Color(0xb7a5ef);

        // Default: a spatial infinity loop. ?sculpture=orbit keeps a second
        // elliptical, twisted ribbon option for artistic A/B review.
        const orbitVariant = new URLSearchParams(window.location.search).get('sculpture') === 'orbit';
        const up = new THREE.Vector3(0, 0, 1);
        const ribbonPoint = (angle, v) => {
            if (orbitVariant) {
                const twist = Math.cos(angle * .5);
                const r = 1.93 + v * twist;
                return new THREE.Vector3(
                    r * Math.cos(angle) * 1.32,
                    r * Math.sin(angle) * .84,
                    v * Math.sin(angle * .5) + .28 * Math.sin(angle * 2)
                );
            }
            const center = new THREE.Vector3(
                2.25 * Math.sin(angle),
                1.16 * Math.sin(2 * angle),
                .52 * Math.cos(angle)
            );
            const tangent = new THREE.Vector3(
                2.25 * Math.cos(angle),
                2.32 * Math.cos(2 * angle),
                -.52 * Math.sin(angle)
            ).normalize();
            const side = new THREE.Vector3().crossVectors(tangent, up).normalize();
            const normal = new THREE.Vector3().crossVectors(tangent, side).normalize();
            const twist = angle * .5 + .28;
            return center
                .addScaledVector(side, v * Math.cos(twist))
                .addScaledVector(normal, v * Math.sin(twist));
        };

        for (let i = 0; i <= sections; i++) {
            const a = (i / sections) * Math.PI * 2;
            for (let j = 0; j <= widthSections; j++) {
                const v = (j / widthSections - .5) * 1.1;
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
            metalness: .73, roughness: .095, clearcoat: 1,
            clearcoatRoughness: .08, envMapIntensity: 2.05,
            transparent: true, opacity: .7, depthWrite: false
        });
        sculpture.add(new THREE.Mesh(geometry, material));

        const contourGeometries = [], contourMaterials = [];
        for (let c = 0; c <= 8; c++) {
            const v = (c / 8 - .5) * 1.1;
            const coords = [];
            for (let n = 0; n <= sections; n++) {
                const p = ribbonPoint(n / sections * Math.PI * 2, v);
                coords.push(p.x, p.y, p.z);
            }
            const lineGeometry = new THREE.BufferGeometry();
            lineGeometry.setAttribute('position', new THREE.Float32BufferAttribute(coords, 3));
            const lineMaterial = new THREE.LineBasicMaterial({
                color: c % 3 === 0 ? 0xb2dfff : 0xb5a2f7,
                transparent: true, opacity: c === 0 || c === 8 ? .45 : .13,
                depthWrite: false
            });
            sculpture.add(new THREE.Line(lineGeometry, lineMaterial));
            contourGeometries.push(lineGeometry);
            contourMaterials.push(lineMaterial);
        }
        scene.add(sculpture);
        scene.add(new THREE.AmbientLight(0xa0bbff, .6));
        const blue = new THREE.PointLight(0x68caff, 36);
        blue.position.set(-3.8, 2.6, 4.8); scene.add(blue);
        const violet = new THREE.PointLight(0x7559ec, 30);
        violet.position.set(3, -2.7, 3.9); scene.add(violet);
        const rim = new THREE.PointLight(0xc8deff, 25);
        rim.position.set(1.9, 1.7, -3.9); scene.add(rim);

        const target = {x: 0, y: 0};
        let visible = true;
        const started = performance.now();
        let disposed = false;
        const resize = () => {
            const w = Math.max(1, art.clientWidth), h = Math.max(1, art.clientHeight);
            camera.aspect = w / h; camera.updateProjectionMatrix();
            renderer.setSize(w, h, false);
            sculpture.scale.setScalar(w < 580 ? .79 : 1.03);
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
            environmentTarget.dispose();
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
                const x = -.21 + Math.sin(t * .48) * .19 + target.x;
                const y = -.25 + t * .075 + Math.cos(t * .34) * .2 + target.y;
                const z = -.33 + Math.sin(t * .3) * .095;
                sculpture.rotation.x += (x - sculpture.rotation.x) * .055;
                sculpture.rotation.y += (y - sculpture.rotation.y) * .055;
                sculpture.rotation.z += (z - sculpture.rotation.z) * .055;
                renderer.render(scene, camera);
            }
            requestAnimationFrame(draw);
        };
        requestAnimationFrame(draw);
    }).catch(() => {
        // CSS fallback intentionally stays visible if import or WebGL fails.
    });
})();