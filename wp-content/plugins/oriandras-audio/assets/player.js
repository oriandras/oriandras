(function(){
    function qs(id){ return document.getElementById(id); }
    function $$ (sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
    function secondsFrom(val){
        if(!val && val!==0) return 0;
        if(typeof val === 'number') return Math.max(0, val|0);
        if(/^\d+$/.test(val)) return parseInt(val,10);
        var parts = String(val).split(':').map(function(p){return parseInt(p,10)||0;});
        if(parts.length===3) return parts[0]*3600+parts[1]*60+parts[2];
        if(parts.length===2) return parts[0]*60+parts[1];
        return 0;
    }

    function setup(container){
        var root = qs(container.id);
        if(!root) return;
        var audio = root.querySelector('.ori-audio__element');
        var listButtons = $$('.ori-audio__track', root);
        var now = root.querySelector('.ori-audio__now');
        var ctaWrap = root.querySelector('.ori-audio__cta');
        var ctaLink = root.querySelector('.ori-audio__cta-link');
        var btnPrev = root.querySelector('.ori-prev');
        var btnNext = root.querySelector('.ori-next');
        var btnPlay = root.querySelector('.ori-play');
        var btnPause = root.querySelector('.ori-pause');
        var btnStop = root.querySelector('.ori-stop');
        var currentIndex = 0;

        function announce(text){ if(now){ now.textContent = text; } }

        function updateActive(){
            listButtons.forEach(function(b){ b.removeAttribute('aria-current'); });
            var active = listButtons[currentIndex];
            if(active){ active.setAttribute('aria-current','true'); active.focus({preventScroll:true}); }
        }

        function load(index){
            if(index<0||index>=listButtons.length) return;
            currentIndex = index;
            var btn = listButtons[index];
            var url = btn.getAttribute('data-url');
            var title = btn.getAttribute('data-title')||'';
            var desc = btn.getAttribute('data-desc')||'';
            var start = parseInt(btn.getAttribute('data-start')||'0',10) || 0;
            var ctaLabel = btn.getAttribute('data-cta-label')||'';
            var ctaUrl = btn.getAttribute('data-cta-url')||'';

            audio.src = url;
            audio.currentTime = 0;
            audio.addEventListener('loadedmetadata', function onMeta(){
                audio.removeEventListener('loadedmetadata', onMeta);
                try{ audio.currentTime = start; }catch(e){}
            });
            announce('Now playing: ' + title + (desc?('. '+desc):''));
            if(ctaWrap){
                if(ctaLabel && ctaUrl){
                    ctaLink.textContent = ctaLabel;
                    ctaLink.href = ctaUrl;
                    ctaWrap.hidden = false;
                } else {
                    ctaWrap.hidden = true;
                }
            }
            updateActive();
        }

        function play(){ audio.play().catch(function(){}); btnPlay.setAttribute('aria-pressed','true'); }
        function pause(){ audio.pause(); btnPlay.setAttribute('aria-pressed','false'); }
        function stop(){ audio.pause(); audio.currentTime = 0; btnPlay.setAttribute('aria-pressed','false'); }
        function next(){ load((currentIndex+1)%listButtons.length); play(); }
        function prev(){ load((currentIndex-1+listButtons.length)%listButtons.length); play(); }

        listButtons.forEach(function(b, i){
            b.addEventListener('click', function(){ load(i); play(); });
            b.addEventListener('keydown', function(e){
                if(e.key==='ArrowDown'){ e.preventDefault(); var ni=(i+1)%listButtons.length; listButtons[ni].focus(); }
                if(e.key==='ArrowUp'){ e.preventDefault(); var pi=(i-1+listButtons.length)%listButtons.length; listButtons[pi].focus(); }
                if(e.key==='Enter' || e.key===' '){ e.preventDefault(); load(i); play(); }
            });
        });

        btnPrev && btnPrev.addEventListener('click', prev);
        btnNext && btnNext.addEventListener('click', next);
        btnPlay && btnPlay.addEventListener('click', play);
        btnPause && btnPause.addEventListener('click', pause);
        btnStop && btnStop.addEventListener('click', stop);

        audio.addEventListener('ended', next);

        // initialize
        load(0);
    }

    function init(){
        if(!window.OriAudioPlaylists) return;
        window.OriAudioPlaylists.forEach(setup);
    }

    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded', init);
    } else { init(); }
})();