/**
 * 아로스 버튼 — 프론트엔드 JS
 * adsensefarm_child 테마 전용
 */
(function () {
    'use strict';

    function initArosButtons() {
        var buttons = document.querySelectorAll('.aros-button');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {

            /* 클릭 시 시각 피드백 (ripple) */
            btn.addEventListener('click', function (e) {
                var ripple = document.createElement('span');
                ripple.style.cssText = [
                    'position:absolute',
                    'border-radius:50%',
                    'background:rgba(255,255,255,0.4)',
                    'transform:scale(0)',
                    'animation:aros-ripple .55s linear',
                    'pointer-events:none',
                    'width:60px', 'height:60px',
                    'margin-top:-30px', 'margin-left:-30px',
                    'top:' + (e.offsetY || 30) + 'px',
                    'left:' + (e.offsetX || 30) + 'px',
                ].join(';');

                /* position:relative 보장 */
                if (getComputedStyle(btn).position === 'static') {
                    btn.style.position = 'relative';
                }
                btn.style.overflow = 'hidden';
                btn.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 600);
            });

            /* 외부 링크 noopener 자동 보완 */
            var href = btn.getAttribute('href');
            if (href && href.startsWith('http')) {
                var rel = btn.getAttribute('rel') || '';
                if (!rel.includes('noopener')) {
                    btn.setAttribute('rel', (rel + ' noopener').trim());
                }
            }
        });

        /* Ripple keyframe 주입 (한 번만) */
        if (!document.getElementById('aros-ripple-style')) {
            var style = document.createElement('style');
            style.id  = 'aros-ripple-style';
            style.textContent = '@keyframes aros-ripple{to{transform:scale(8);opacity:0}}';
            document.head.appendChild(style);
        }
    }

    /* DOMContentLoaded 또는 즉시 실행 */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initArosButtons);
    } else {
        initArosButtons();
    }
})();
