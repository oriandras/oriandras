(function(){
  var toggle = document.getElementById('nav-toggle');
  var drawer = document.getElementById('mobile-nav');
  var overlay = document.getElementById('nav-overlay');
  var closeBtn = document.getElementById('nav-close');
  if(!drawer) return;

  function openNav(){
    if (!drawer.classList.contains('-translate-x-full')) return;
    drawer.classList.remove('-translate-x-full');
    overlay && overlay.classList.remove('hidden');
    if (toggle) toggle.setAttribute('aria-expanded','true');
    document.body.classList.add('overflow-hidden');
  }
  function closeNav(){
    if (drawer.classList.contains('-translate-x-full')) return;
    drawer.classList.add('-translate-x-full');
    overlay && overlay.classList.add('hidden');
    if (toggle) toggle.setAttribute('aria-expanded','false');
    document.body.classList.remove('overflow-hidden');
  }

  toggle && toggle.addEventListener('click', function(e){ e.preventDefault(); openNav(); });
  closeBtn && closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeNav(); });
  overlay && overlay.addEventListener('click', function(){ closeNav(); });

  // Close on Esc
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeNav(); }});

  // Basic swipe gestures (open by swiping right from left edge; close by swiping left on drawer)
  var startX = 0, currentX = 0, touching = false, threshold = 60, edge = 24;

  // Detect open gesture from page body
  document.addEventListener('touchstart', function(e){
    if (e.touches.length !== 1) return;
    startX = e.touches[0].clientX;
    touching = true;
  }, {passive:true});
  document.addEventListener('touchmove', function(e){
    if (!touching) return;
    currentX = e.touches[0].clientX;
  }, {passive:true});
  document.addEventListener('touchend', function(){
    if (!touching) return;
    var deltaX = currentX - startX;
    if (startX <= edge && deltaX > threshold) {
      openNav();
    }
    touching = false; startX = 0; currentX = 0;
  });

  // Detect close gesture on drawer
  var dStartX = 0, dCurrentX = 0, dTouching = false;
  drawer.addEventListener('touchstart', function(e){
    if (e.touches.length !== 1) return;
    dStartX = e.touches[0].clientX; dTouching = true;
  }, {passive:true});
  drawer.addEventListener('touchmove', function(e){
    if (!dTouching) return; dCurrentX = e.touches[0].clientX;
  }, {passive:true});
  drawer.addEventListener('touchend', function(){
    if (!dTouching) return; var dDeltaX = dCurrentX - dStartX;
    if (dDeltaX < -threshold) { closeNav(); }
    dTouching = false; dStartX = 0; dCurrentX = 0;
  });

  // Mobile accordion for submenus
  function setupMobileAccordion(){
    var container = drawer;
    if (!container) return;
    var items = container.querySelectorAll('li.menu-item-has-children, li.has-children');
    Array.prototype.forEach.call(items, function(li){
      var toggleEl = li.querySelector(':scope > button, :scope > a');
      var submenu = li.querySelector(':scope > ul.sub-menu');
      if (!toggleEl || !submenu) return;
      submenu.classList.add('hidden');
      toggleEl.addEventListener('click', function(e){
        // Only intercept clicks when menu is visible (mobile context)
        var isMobileOpen = !drawer.classList.contains('-translate-x-full');
        if (!isMobileOpen) return; // allow normal behavior on desktop if any
        e.preventDefault();
        var isHidden = submenu.classList.toggle('hidden');
        if (toggleEl.tagName === 'BUTTON') toggleEl.setAttribute('aria-expanded', String(!isHidden));
      });
    });
  }

  // Desktop dropdowns: open on click or focus (no hover)
  function setupDesktopDropdowns(){
    var primaryNav = document.querySelector('nav[aria-label="Primary"]');
    if (!primaryNav) return;
    var items = primaryNav.querySelectorAll('li.menu-item-has-children, li.has-children');

    function closeItem(li){
      var submenu = li.querySelector(':scope > ul.sub-menu');
      var toggleEl = li.querySelector(':scope > button, :scope > a');
      if (submenu) submenu.classList.add('hidden');
      if (toggleEl && toggleEl.tagName === 'BUTTON') toggleEl.setAttribute('aria-expanded','false');
    }
    function openItem(li){
      var submenu = li.querySelector(':scope > ul.sub-menu');
      var toggleEl = li.querySelector(':scope > button, :scope > a');
      if (!submenu) return;
      submenu.classList.remove('hidden');
      if (toggleEl && toggleEl.tagName === 'BUTTON') toggleEl.setAttribute('aria-expanded','true');
      if (li.getAttribute('data-has-grandchildren') === '1'){
        submenu.classList.add('grid','grid-cols-2','md:grid-cols-3');
      }
    }

    Array.prototype.forEach.call(items, function(li){
      var toggleEl = li.querySelector(':scope > button, :scope > a');
      var submenu = li.querySelector(':scope > ul.sub-menu');
      if (!toggleEl || !submenu) return;

      // Click toggles
      toggleEl.addEventListener('click', function(e){
        // only when desktop nav is visible
        var desktopVisible = window.matchMedia('(min-width: 768px)').matches;
        if (!desktopVisible) return; // mobile handled elsewhere
        e.preventDefault();
        var isHidden = submenu.classList.contains('hidden');
        // close siblings
        var siblings = li.parentElement ? li.parentElement.children : [];
        Array.prototype.forEach.call(siblings, function(sib){ if (sib !== li) closeItem(sib); });
        if (isHidden) openItem(li); else closeItem(li);
      });

      // Focus handling: show on focus within, hide on leaving
      li.addEventListener('focusin', function(){
        var desktopVisible = window.matchMedia('(min-width: 768px)').matches;
        if (!desktopVisible) return;
        openItem(li);
      });
      li.addEventListener('focusout', function(e){
        var desktopVisible = window.matchMedia('(min-width: 768px)').matches;
        if (!desktopVisible) return;
        // Delay to allow focusing into submenu children
        setTimeout(function(){
          if (!li.contains(document.activeElement)){
            closeItem(li);
          }
        }, 50);
      });
    });

    // Close on outside click
    document.addEventListener('click', function(e){
      var desktopVisible = window.matchMedia('(min-width: 768px)').matches;
      if (!desktopVisible) return;
      if (!primaryNav.contains(e.target)){
        Array.prototype.forEach.call(items, closeItem);
      }
    });

    // Close on Esc
    document.addEventListener('keydown', function(e){
      var desktopVisible = window.matchMedia('(min-width: 768px)').matches;
      if (!desktopVisible) return;
      if (e.key === 'Escape'){
        Array.prototype.forEach.call(items, closeItem);
      }
    });
  }

  setupMobileAccordion();
  setupDesktopDropdowns();
})();
