(function(){
  function initCarousel(root){
    if (!root) return;
    var track = root.querySelector('.oriandras-crsl-track');
    var slides = Array.prototype.slice.call(root.querySelectorAll('.oriandras-crsl-slide'));
    if (!track || slides.length === 0) return;

    var index = 0;
    var count = slides.length;

    var live = root.querySelector('.oriandras-crsl-live');
    function announce(text){ if (live) { live.textContent = text; } }

    function update(){
      var offset = -index * 100;
      track.style.transform = 'translateX(' + offset + '%)';

      // Mark hidden/visible slides and manage focusability
      for (var s=0; s<slides.length; s++){
        var slide = slides[s];
        var link = slide.querySelector('.oriandras-crsl-link');
        if (s === index){
          slide.removeAttribute('aria-hidden');
          if (link) link.removeAttribute('tabindex');
        } else {
          slide.setAttribute('aria-hidden', 'true');
          if (link) link.setAttribute('tabindex', '-1');
        }
      }

      // update dots aria-current
      var dotsWrap = root.querySelector('.oriandras-crsl-dots');
      if (dotsWrap){
        var dots = dotsWrap.querySelectorAll('.oriandras-crsl-dot');
        for (var i=0;i<dots.length;i++){
          if (i === index){ dots[i].setAttribute('aria-current', 'true'); }
          else { dots[i].removeAttribute('aria-current'); }
        }
      }

      // Announce current slide position
      announce('Slide ' + (index+1) + ' of ' + count);
    }

    function prev(){ index = (index - 1 + count) % count; update(); }
    function next(){ index = (index + 1) % count; update(); }

    var prevBtn = root.querySelector('.oriandras-crsl-prev');
    var nextBtn = root.querySelector('.oriandras-crsl-next');
    if (prevBtn) prevBtn.addEventListener('click', prev);
    if (nextBtn) nextBtn.addEventListener('click', next);

    // keyboard left/right when focused inside carousel
    root.addEventListener('keydown', function(e){
      if (e.key === 'ArrowLeft'){ prev(); }
      else if (e.key === 'ArrowRight'){ next(); }
    });

    // Add dots if requested
    var wantDots = root.getAttribute('data-dots') === 'true';
    if (wantDots && count > 1){
      var dotsWrap = document.createElement('div');
      dotsWrap.className = 'oriandras-crsl-dots';
      for (var i=0;i<count;i++){
        (function(i){
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'oriandras-crsl-dot';
          b.setAttribute('aria-label', 'Go to slide ' + (i+1));
          b.addEventListener('click', function(){ index = i; update(); });
          dotsWrap.appendChild(b);
        })(i);
      }
      root.appendChild(dotsWrap);
    }

    // Make the region focusable for keyboard navigation
    var viewport = root.querySelector('.oriandras-crsl-viewport');
    if (viewport && !viewport.hasAttribute('tabindex')){
      viewport.setAttribute('tabindex', '0');
    }

    update();
  }

  function initAll(){
    var carousels = document.querySelectorAll('.oriandras-crsl');
    for (var i=0;i<carousels.length;i++){
      initCarousel(carousels[i]);
    }
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
