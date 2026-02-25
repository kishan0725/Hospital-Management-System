
(function () {
    'use strict';


    const isMobile = window.matchMedia('(max-width: 576px)').matches;
    const isTablet = window.matchMedia('(min-width: 577px) and (max-width: 992px)').matches;
    const petalCount = isMobile ? 15 : isTablet ? 30 : 50;
    const petalImage =
        'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEizrrtX-KQtKY8e8pxCHjLROT5pYW7sVkUpET9HHpW8QO-PnoIRKVsvRDxM6shrE4Q-44Oh9teSGK1SApaZ1OJvhR4z7ENgKSJOLWfsdKw9jPszAa2HqaE6W8ohyGHRvff6TgKXEUjnn73LLLp3FHbtMTJnIkPxPhujWwG5ZsFgW7ctQ0zrR5KKSqlewg/s16000/hoadao-anonyviet.com.png';

    const petals = [];
    let docWidth = window.innerWidth;
    let docHeight = window.innerHeight;


    for (let i = 0; i < petalCount; i++) {
        const size = 10 + Math.random() * 14;
        const opacity = 0.5 + Math.random() * 0.4;
        const rotation = Math.random() * 360;
        const blur = Math.random() * 0.5;

        const petal = {
            x: Math.random() * docWidth,
            y: Math.random() * docHeight - docHeight,
            dx: 0,
            rotation: rotation,
            rotationSpeed: (Math.random() - 0.5) * 1.5,
            amplitude: 20 + Math.random() * 35,
            speedX: 0.01 + Math.random() / 15,
            speedY: 0.3 + Math.random() * 0.6,
            size: size,
            opacity: opacity,
            blur: blur,
            element: null,
        };

        const div = document.createElement('div');
        div.id = 'cherry-petal-' + i;
        div.style.cssText = `position:fixed;z-index:9998;visibility:visible;pointer-events:none;width:${size}px;left:${petal.x}px;top:${petal.y}px;opacity:${opacity};transition:transform 0.15s ease-out;will-change:transform,top,left`;
        div.innerHTML = `<img src="${petalImage}" alt="Hoa đào" style="width:100%;height:auto;transform:rotate(${rotation}deg);filter:drop-shadow(2px 2px 4px rgba(255,105,180,0.4)) blur(${blur}px) brightness(1.1);">`;
        document.body.appendChild(div);
        petal.element = div;
        petals.push(petal);
    }


    function animate() {
        docWidth = window.innerWidth;
        docHeight = window.innerHeight;

        petals.forEach((petal) => {
            petal.y += petal.speedY;
            petal.rotation += petal.rotationSpeed;


            if (petal.y > docHeight + 80) {
                petal.x = Math.random() * docWidth;
                petal.y = -80;
                petal.speedX = 0.01 + Math.random() / 15;
                petal.speedY = 0.3 + Math.random() * 0.6;
                petal.rotationSpeed = (Math.random() - 0.5) * 1.5;
            }


            petal.dx += petal.speedX;
            const swayX = petal.x + petal.amplitude * Math.sin(petal.dx);
            const scaleEffect = 0.95 + Math.sin(petal.dx * 2) * 0.05;

            petal.element.style.top = petal.y + 'px';
            petal.element.style.left = swayX + 'px';
            petal.element.querySelector('img').style.transform = `rotate(${petal.rotation}deg) scale(${scaleEffect})`;
        });

        requestAnimationFrame(animate);
    }


    animate();


    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            docWidth = window.innerWidth;
            docHeight = window.innerHeight;
        }, 250);
    });
})();
