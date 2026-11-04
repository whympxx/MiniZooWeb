/* =========================================================
   Zoo 3D — efek tilt, animasi masuk, count-up, dan scene Three.js
   ========================================================= */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia && window.matchMedia('(pointer: fine)').matches;

    /* ---------- Tilt 3D mengikuti kursor ---------- */
    function initTilt(root) {
        if (reduceMotion || !finePointer) return;
        (root || document).querySelectorAll('[data-tilt]').forEach(function (el) {
            if (el.__tilt) return;
            el.__tilt = true;
            var max = parseFloat(el.getAttribute('data-tilt')) || 10;
            if (!el.querySelector(':scope > .glare')) {
                var glare = document.createElement('div');
                glare.className = 'glare';
                el.appendChild(glare);
            }
            el.addEventListener('pointermove', function (e) {
                var r = el.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width;
                var y = (e.clientY - r.top) / r.height;
                el.style.setProperty('--ry', ((x - 0.5) * max * 2).toFixed(2) + 'deg');
                el.style.setProperty('--rx', ((0.5 - y) * max * 2).toFixed(2) + 'deg');
                el.style.setProperty('--gx', (x * 100).toFixed(1) + '%');
                el.style.setProperty('--gy', (y * 100).toFixed(1) + '%');
                el.classList.add('is-tilting');
            });
            el.addEventListener('pointerleave', function () {
                el.style.setProperty('--rx', '0deg');
                el.style.setProperty('--ry', '0deg');
                el.classList.remove('is-tilting');
            });
        });
    }

    /* ---------- Animasi masuk saat discroll ---------- */
    function initReveal() {
        var items = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window) || reduceMotion) {
            items.forEach(function (el) { el.classList.add('in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    el.classList.add('in');
                    // Hapus jeda stagger setelah animasi masuk agar tilt terasa responsif
                    setTimeout(function () { el.removeAttribute('data-delay'); }, 1500);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        items.forEach(function (el) { io.observe(el); });
    }

    /* ---------- Angka berjalan ---------- */
    function initCountUp() {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-count')) || 0;
            var decimals = (el.getAttribute('data-count').split('.')[1] || '').length;
            var suffix = el.getAttribute('data-suffix') || '';
            var fmt = function (v) {
                return v.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
            };
            if (reduceMotion) { el.textContent = fmt(target); return; }
            var start = null, dur = 1400;
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                el.textContent = fmt(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    /* ---------- Daun berjatuhan di latar ---------- */
    function initLeaves() {
        var box = document.querySelector('.zoo-scenery');
        if (!box || reduceMotion) return;
        var leaves = ['🍃', '🌿', '🍂', '🍃'];
        for (var i = 0; i < 9; i++) {
            var leaf = document.createElement('span');
            leaf.className = 'leaf';
            leaf.textContent = leaves[i % leaves.length];
            leaf.style.left = (Math.random() * 100) + '%';
            leaf.style.animationDuration = (14 + Math.random() * 14) + 's';
            leaf.style.animationDelay = (-Math.random() * 25) + 's';
            leaf.style.fontSize = (16 + Math.random() * 14) + 'px';
            box.appendChild(leaf);
        }
    }

    /* ---------- Menu mobile ---------- */
    function initMobileMenu() {
        var btn = document.querySelector('[data-menu-toggle]');
        var menu = document.querySelector('.mobile-menu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function () {
            menu.classList.toggle('open');
            btn.setAttribute('aria-expanded', menu.classList.contains('open'));
        });
    }

    /* ---------- Toggle tampilkan password ---------- */
    function initPasswordToggle() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-password'));
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
                btn.textContent = input.type === 'password' ? '🙈' : '🐵';
            });
        });
    }

    /* =========================================================
       Scene Three.js: pulau safari low-poly yang melayang
       ========================================================= */
    function hasWebGL() {
        try {
            var c = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
        } catch (e) { return false; }
    }

    function buildScene(container) {
        var THREE = window.THREE;
        var compact = container.hasAttribute('data-compact');

        var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.domElement.className = 'zoo-scene';
        container.prepend(renderer.domElement);

        var scene = new THREE.Scene();
        scene.fog = new THREE.Fog(0xe0f7d4, 30, 60);

        var camera = new THREE.PerspectiveCamera(40, 1, 0.1, 200);
        var camBase = compact ? new THREE.Vector3(0, 7, 19) : new THREE.Vector3(0, 8, 22);
        camera.position.copy(camBase);

        scene.add(new THREE.HemisphereLight(0xfff7d6, 0x3f9a4f, 0.9));
        var sun = new THREE.DirectionalLight(0xffffff, 0.85);
        sun.position.set(8, 14, 6);
        sun.castShadow = true;
        sun.shadow.mapSize.set(1024, 1024);
        sun.shadow.camera.left = -10; sun.shadow.camera.right = 10;
        sun.shadow.camera.top = 10; sun.shadow.camera.bottom = -10;
        scene.add(sun);

        function mat(color) { return new THREE.MeshStandardMaterial({ color: color, flatShading: true, roughness: 0.9 }); }
        function mesh(geo, color, shadow) {
            var m = new THREE.Mesh(geo, mat(color));
            m.castShadow = shadow !== false;
            m.receiveShadow = true;
            return m;
        }

        var island = new THREE.Group();
        scene.add(island);

        // Tanah pulau
        var top = mesh(new THREE.CylinderGeometry(6, 5.4, 1.2, 10), 0x6cc070, false);
        top.receiveShadow = true;
        island.add(top);
        var dirt = mesh(new THREE.ConeGeometry(5.4, 4.5, 10), 0x8b5a2b, false);
        dirt.rotation.x = Math.PI;
        dirt.position.y = -2.85;
        island.add(dirt);
        var rockBottom = mesh(new THREE.ConeGeometry(2.2, 2.5, 7), 0x6b4423, false);
        rockBottom.rotation.x = Math.PI;
        rockBottom.position.y = -5.6;
        island.add(rockBottom);

        // Kolam
        var pond = new THREE.Mesh(new THREE.CylinderGeometry(1.6, 1.6, 0.1, 18),
            new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.2, metalness: 0.1, flatShading: true }));
        pond.position.set(2.3, 0.62, 1.4);
        pond.receiveShadow = true;
        island.add(pond);

        // Pohon
        function tree(x, z, s, type) {
            var g = new THREE.Group();
            var trunk = mesh(new THREE.CylinderGeometry(0.15 * s, 0.22 * s, 1.2 * s, 6), 0x7c4a1e);
            trunk.position.y = 0.6 * s;
            g.add(trunk);
            if (type === 'pine') {
                for (var i = 0; i < 3; i++) {
                    var c = mesh(new THREE.ConeGeometry((1 - i * 0.25) * s, 1.2 * s, 7), i % 2 ? 0x22c55e : 0x16a34a);
                    c.position.y = (1.3 + i * 0.6) * s;
                    g.add(c);
                }
            } else if (type === 'palm') {
                trunk.scale.y = 2;
                trunk.position.y = 1.2 * s;
                for (var j = 0; j < 5; j++) {
                    var leaf = mesh(new THREE.ConeGeometry(0.25 * s, 1.6 * s, 4), 0x4ade80);
                    leaf.position.y = 2.4 * s;
                    leaf.rotation.z = Math.PI / 2.6;
                    var holder = new THREE.Group();
                    holder.rotation.y = (j / 5) * Math.PI * 2;
                    leaf.position.x = 0.7 * s;
                    holder.add(leaf);
                    g.add(holder);
                }
            } else {
                var canopy = mesh(new THREE.DodecahedronGeometry(0.9 * s, 0), 0x22a447);
                canopy.position.y = 1.7 * s;
                g.add(canopy);
                var canopy2 = mesh(new THREE.DodecahedronGeometry(0.6 * s, 0), 0x4ade80);
                canopy2.position.set(0.5 * s, 2.1 * s, 0.2 * s);
                g.add(canopy2);
            }
            g.position.set(x, 0.6, z);
            g.userData.sway = Math.random() * Math.PI * 2;
            island.add(g);
            return g;
        }
        var trees = [
            tree(-3.6, -2, 1.2, 'pine'), tree(-2.2, -3.6, 1, 'round'), tree(3.8, -2.6, 1.1, 'palm'),
            tree(-4.4, 1.4, 0.9, 'round'), tree(0.6, -4.3, 0.9, 'pine'), tree(4.6, 0.3, 0.8, 'round')
        ];

        // Batu
        [[-1.2, 3.8, 0.5], [3.9, 2.9, 0.35], [-3.3, 3.2, 0.4]].forEach(function (r) {
            var rock = mesh(new THREE.DodecahedronGeometry(r[2], 0), 0x9ca3af);
            rock.position.set(r[0], 0.6 + r[2] * 0.6, r[1]);
            island.add(rock);
        });

        // Jerapah low-poly
        function giraffe() {
            var g = new THREE.Group();
            var yellow = 0xf6c453, spot = 0xb7791f;
            var body = mesh(new THREE.BoxGeometry(1.4, 0.7, 0.6), yellow);
            body.position.y = 1.5;
            g.add(body);
            [[-0.5, -0.2], [0.5, -0.2], [-0.5, 0.2], [0.5, 0.2]].forEach(function (p) {
                var leg = mesh(new THREE.BoxGeometry(0.16, 1.2, 0.16), yellow);
                leg.position.set(p[0], 0.6, p[1]);
                g.add(leg);
            });
            var neckPivot = new THREE.Group();
            neckPivot.position.set(0.55, 1.75, 0);
            var neck = mesh(new THREE.BoxGeometry(0.28, 1.7, 0.28), yellow);
            neck.position.y = 0.85;
            neckPivot.add(neck);
            var head = mesh(new THREE.BoxGeometry(0.6, 0.32, 0.32), yellow);
            head.position.set(0.18, 1.8, 0);
            neckPivot.add(head);
            [-0.1, 0.1].forEach(function (z) {
                var horn = mesh(new THREE.CylinderGeometry(0.04, 0.04, 0.22, 4), spot);
                horn.position.set(0.02, 2.05, z);
                neckPivot.add(horn);
            });
            neckPivot.rotation.z = -0.25;
            g.add(neckPivot);
            for (var i = 0; i < 4; i++) {
                var s = mesh(new THREE.BoxGeometry(0.25, 0.05, 0.25), spot, false);
                s.position.set(-0.45 + i * 0.3, 1.86, (i % 2 ? 0.1 : -0.12));
                g.add(s);
            }
            g.userData.neck = neckPivot;
            return g;
        }
        var gir = giraffe();
        gir.position.set(-1.4, 0.6, 0.4);
        gir.rotation.y = 0.6;
        island.add(gir);

        // Gajah low-poly
        function elephant() {
            var g = new THREE.Group();
            var grey = 0x9aa5b1;
            var body = mesh(new THREE.BoxGeometry(1.5, 1, 0.95), grey);
            body.position.y = 1.15;
            g.add(body);
            [[-0.5, -0.3], [0.5, -0.3], [-0.5, 0.3], [0.5, 0.3]].forEach(function (p) {
                var leg = mesh(new THREE.CylinderGeometry(0.17, 0.19, 0.7, 6), grey);
                leg.position.set(p[0], 0.35, p[1]);
                g.add(leg);
            });
            var head = mesh(new THREE.BoxGeometry(0.7, 0.75, 0.75), grey);
            head.position.set(0.95, 1.35, 0);
            g.add(head);
            [-0.45, 0.45].forEach(function (z) {
                var ear = mesh(new THREE.BoxGeometry(0.08, 0.65, 0.5), 0x8b96a3);
                ear.position.set(0.75, 1.4, z);
                g.add(ear);
            });
            var trunkPivot = new THREE.Group();
            trunkPivot.position.set(1.28, 1.15, 0);
            var trunk = mesh(new THREE.CylinderGeometry(0.1, 0.14, 0.8, 6), grey);
            trunk.position.y = -0.4;
            trunkPivot.add(trunk);
            g.add(trunkPivot);
            g.userData.trunk = trunkPivot;
            return g;
        }
        var ele = elephant();
        ele.position.set(1.2, 0.6, -1.3);
        ele.rotation.y = -2.4;
        ele.scale.setScalar(0.85);
        island.add(ele);

        // Burung terbang mengitari pulau
        var birds = [];
        for (var b = 0; b < 4; b++) {
            var bird = new THREE.Group();
            var wingGeo = new THREE.BufferGeometry();
            wingGeo.setAttribute('position', new THREE.Float32BufferAttribute([0, 0, 0, 0.6, 0, 0.18, 0, 0, 0.36], 3));
            wingGeo.computeVertexNormals();
            var wmat = new THREE.MeshStandardMaterial({ color: b % 2 ? 0xf97316 : 0x1e3a2b, side: THREE.DoubleSide, flatShading: true });
            var left = new THREE.Mesh(wingGeo, wmat);
            var right = new THREE.Mesh(wingGeo, wmat);
            right.scale.x = -1;
            bird.add(left, right);
            bird.userData = { left: left, right: right, r: 7 + b * 1.1, h: 4 + b * 0.7, speed: 0.25 + b * 0.05, phase: b * 1.7 };
            scene.add(bird);
            birds.push(bird);
        }

        // Daun melayang (partikel)
        var leaves = [];
        for (var l = 0; l < (compact ? 10 : 18); l++) {
            var lf = mesh(new THREE.TetrahedronGeometry(0.15 + Math.random() * 0.12, 0), l % 3 ? 0x4ade80 : 0xfacc15, false);
            lf.position.set((Math.random() - 0.5) * 22, Math.random() * 10 - 2, (Math.random() - 0.5) * 10);
            lf.userData = { speed: 0.3 + Math.random() * 0.5, spin: Math.random() * 0.04 };
            scene.add(lf);
            leaves.push(lf);
        }

        // Ukuran kanvas mengikuti container
        function resize() {
            // Ukuran tampilan kanvas diatur CSS (bisa lebih kecil dari container di HP)
            var w = renderer.domElement.clientWidth, h = renderer.domElement.clientHeight;
            if (!w || !h) return;
            renderer.setSize(w, h, false);
            camera.aspect = w / h;
            // Mundurkan kamera di layar sempit agar pulau tetap utuh
            camera.position.z = camBase.z * Math.max(1, 1.25 / Math.min(camera.aspect, 1.25));
            camera.updateProjectionMatrix();
        }
        resize();
        if ('ResizeObserver' in window) new ResizeObserver(resize).observe(renderer.domElement);
        else window.addEventListener('resize', resize);

        // Kamera sedikit mengikuti mouse
        var mouse = { x: 0, y: 0 };
        container.addEventListener('pointermove', function (e) {
            var r = container.getBoundingClientRect();
            mouse.x = ((e.clientX - r.left) / r.width - 0.5) * 2;
            mouse.y = ((e.clientY - r.top) / r.height - 0.5) * 2;
        });

        var visible = true;
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(container);
        }

        var clock = new THREE.Clock();
        function frame() {
            requestAnimationFrame(frame);
            if (!visible) return;
            var t = clock.getElapsedTime();

            island.rotation.y = t * 0.12;
            island.position.y = Math.sin(t * 0.8) * 0.25 + (compact ? -0.5 : 0);

            trees.forEach(function (tr) { tr.rotation.z = Math.sin(t * 1.4 + tr.userData.sway) * 0.03; });
            gir.userData.neck.rotation.z = -0.25 + Math.sin(t * 1.2) * 0.12;
            ele.userData.trunk.rotation.z = Math.sin(t * 2) * 0.35;

            birds.forEach(function (bd) {
                var d = bd.userData, a = t * d.speed + d.phase;
                bd.position.set(Math.cos(a) * d.r, d.h + Math.sin(t * 1.5 + d.phase) * 0.4, Math.sin(a) * d.r);
                bd.rotation.y = -a;
                var flap = Math.sin(t * 10 + d.phase) * 0.7;
                d.left.rotation.z = flap;
                d.right.rotation.z = -flap;
            });

            leaves.forEach(function (lf) {
                lf.position.y -= lf.userData.speed * 0.02;
                lf.position.x += Math.sin(t + lf.position.y) * 0.005;
                lf.rotation.x += lf.userData.spin;
                lf.rotation.y += lf.userData.spin;
                if (lf.position.y < -6) lf.position.y = 9;
            });

            camera.position.x += (mouse.x * 3 - camera.position.x) * 0.04;
            camera.position.y += (camBase.y - mouse.y * 2 - camera.position.y) * 0.04;
            camera.lookAt(0, compact ? 0.5 : 1, 0);

            renderer.render(scene, camera);
        }

        if (reduceMotion) {
            camera.lookAt(0, 1, 0);
            renderer.render(scene, camera);
        } else {
            frame();
        }
    }

    function initScenes() {
        var containers = document.querySelectorAll('[data-zoo-scene]');
        if (!containers.length) return;
        if (!window.THREE || !hasWebGL()) {
            containers.forEach(function (c) { c.classList.add('no-webgl'); });
            return;
        }
        containers.forEach(function (c) {
            try { buildScene(c); } catch (e) { console.warn('Scene 3D gagal dimuat:', e); }
        });
    }

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initTilt();
        initReveal();
        initCountUp();
        initLeaves();
        initMobileMenu();
        initPasswordToggle();
        initScenes();
    });

    window.ZooFX = { initTilt: initTilt };
})();
