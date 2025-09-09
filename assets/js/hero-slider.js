// Hero Slider logic (modular version)
(function(){
    function initHeroSlider(){
        const container = document.querySelector('.hero-slider .slider-container');
        if(!container) return; // brak slidera
    const hero = container.closest('.hero-slider');
    const slides = [...container.querySelectorAll('.slide')];
        const dots = [...container.querySelectorAll('.dot')];
        const prevBtn = container.querySelector('.slider-btn.prev');
        const nextBtn = container.querySelector('.slider-btn.next');
        let index = 0;
        let transitioning = false;
        let autoInterval;

        function setActive(newIndex){
            if(transitioning || newIndex === index) return;
            transitioning = true;
            slides[index].classList.remove('active');
            slides[index].setAttribute('aria-hidden','true');
            dots[index].classList.remove('active');
            dots[index].setAttribute('aria-selected','false');
            index = (newIndex + slides.length) % slides.length;
            slides[index].classList.add('active');
            slides[index].setAttribute('aria-hidden','false');
            dots[index].classList.add('active');
            dots[index].setAttribute('aria-selected','true');
            setTimeout(()=>{transitioning=false;},800);
        }
        function next(){ setActive(index+1); }
        function prev(){ setActive(index-1); }
        function goTo(i){ setActive(i); }

    function startAuto(){ autoInterval = setInterval(next,5000); }
        function stopAuto(){ clearInterval(autoInterval); }
        startAuto();

    if(prevBtn) prevBtn.addEventListener('click', prev);
        if(nextBtn) nextBtn.addEventListener('click', next);
        dots.forEach((d,i)=> d.addEventListener('click', ()=> goTo(i)) );
        container.addEventListener('mouseenter', stopAuto);
        container.addEventListener('mouseleave', startAuto);

        // Touch
        let startX=0;
        container.addEventListener('touchstart', e=>{ startX = e.touches[0].clientX; }, {passive:true});
        container.addEventListener('touchend', e=>{ const endX = e.changedTouches[0].clientX; const t = 50; if(startX - endX > t) next(); else if(endX - startX > t) prev(); }, {passive:true});
        if(hero) hero.classList.add('hs-ready');
    }
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initHeroSlider); else initHeroSlider();
})();
