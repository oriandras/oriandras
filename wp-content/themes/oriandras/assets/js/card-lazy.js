(function(){
  if (typeof document === 'undefined') return;

  function markLazy(card){
    if (!card) return;
    card.classList.add('is-lazy');
    card.setAttribute('aria-busy','true');
  }
  function markLoaded(card){
    if (!card) return;
    card.classList.add('is-loaded');
    card.classList.remove('is-lazy');
    card.setAttribute('aria-busy','false');
  }

  function init(){
    var cards = Array.prototype.slice.call(document.querySelectorAll('.ori-card'));
    if (!cards.length) return;

    // Respect reduced motion: skip animation and show content immediately
    var prefersReduced = false;
    try {
      prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch(e) {}

    if (!('IntersectionObserver' in window) || prefersReduced){
      cards.forEach(function(card){ markLoaded(card); });
      return;
    }

    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        var card = entry.target;
        if (entry.isIntersecting){
          // Once visible, mark loaded and stop observing
          markLoaded(card);
          io.unobserve(card);
        }
      });
    }, {
      root: null,
      rootMargin: '100px', // pre-load slightly before it appears
      threshold: 0.01
    });

    cards.forEach(function(card){
      markLazy(card);
      io.observe(card);
    });
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
