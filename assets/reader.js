/**
 * Table of Contents — Floating button + slide-in panel
 *
 * @package piratez_cyberpunk
 */

(function () {
    'use strict';

    const SCROLL_THRESHOLD = 100;
    const TOC_PANEL_ID = 'piratez-toc-panel';
    const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function generateTableOfContents() {
        const tocEnabled = document.body.getAttribute('data-toc-enabled');
        if (window.pzEditorial && !pzEditorial.toc) {
            return;
        }

        const tocRoot = document.getElementById('toc-root');
        const entryContent = (document.querySelector('.pz-editorial-content') || document.querySelector('.entry-content, .wp-block-post-content'));
        if (!tocRoot || !entryContent) {
            return;
        }

        const headings = entryContent.querySelectorAll('h2, h3, h4');
        if (headings.length < 2) {
            return;
        }

        // Ensure headings have IDs
        headings.forEach((heading, index) => {
            if (!heading.id) {
                const slug = heading.textContent.toLowerCase().replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '');
                const base = 'heading-' + index + '-' + slug;
                let id = base;
                let suffix = 1;
                while (document.getElementById(id)) { id = base + '-' + suffix++; }
                heading.id = id;
            }
        });

        // Panel (nav list)
        const panel = document.createElement('div');
        panel.id = TOC_PANEL_ID;
        panel.className = 'piratez-toc-panel';
        panel.setAttribute('role', 'navigation');
        panel.setAttribute('aria-label', 'Table of Contents');
        panel.hidden = true;

        const panelHeader = document.createElement('div');
        panelHeader.className = 'piratez-toc-panel-header';

        const panelTitle = document.createElement('h3');
        panelTitle.className = 'toc-title';
        panelTitle.textContent = 'Table of Contents';
        panelHeader.appendChild(panelTitle);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'piratez-toc-panel-close';
        closeBtn.setAttribute('aria-label', 'Close table of contents');
        closeBtn.innerHTML = '<span aria-hidden="true">&times;</span>';
        panelHeader.appendChild(closeBtn);

        panel.appendChild(panelHeader);

        const tocList = document.createElement('ul');
        tocList.className = 'toc-list';

        headings.forEach((heading) => {
            const tocItem = document.createElement('li');
            tocItem.className = 'toc-item toc-level-' + heading.tagName.toLowerCase();
            const tocLink = document.createElement('a');
            tocLink.href = '#' + encodeURIComponent(heading.id);
            tocLink.textContent = heading.textContent;
            tocLink.className = 'toc-link';
            tocLink.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.getElementById(heading.id);
                if (target) {
                    // Open ancestor disclosures through core's controls before scrolling.
                    const ancestors = [];
                    for (let node = target.parentElement; node && node !== entryContent; node = node.parentElement) { ancestors.unshift(node); }
                    ancestors.forEach(function (node) {
                        if (node.tagName === 'DETAILS') { node.open = true; }
                        if (node.id && (node.hidden || node.getAttribute('aria-hidden') === 'true' || node.classList.contains('wp-block-accordion-panel'))) {
                            const control = Array.from(entryContent.querySelectorAll('[aria-controls]')).find(function (button) {
                                return button.getAttribute('aria-controls').split(/\s+/).includes(node.id);
                            });
                            if (control && (control.getAttribute('aria-expanded') === 'false' || control.getAttribute('aria-selected') === 'false')) { control.click(); }
                        }
                    });
                    requestAnimationFrame(function () {
                        target.scrollIntoView({ behavior: REDUCED_MOTION ? 'auto' : 'smooth', block: 'start' });
                    });
                    history.pushState(null, null, '#' + encodeURIComponent(heading.id));
                }
                if (window.matchMedia('(max-width: 1024px)').matches) {
                    setPanelOpen(false);
                }
            });
            tocItem.appendChild(tocLink);
            tocList.appendChild(tocItem);
        });

        panel.appendChild(tocList);
        tocRoot.appendChild(panel);

        // FAB button
        const fab = document.createElement('button');
        fab.type = 'button';
        fab.className = 'piratez-toc-fab';
        fab.setAttribute('aria-label', 'Table of contents');
        fab.setAttribute('aria-expanded', 'false');
        fab.setAttribute('aria-controls', TOC_PANEL_ID);
        fab.innerHTML = '<span class="piratez-toc-fab-icon" aria-hidden="true">≡</span>';
        tocRoot.insertBefore(fab, panel);

        tocRoot.setAttribute('aria-hidden', 'false');

        // Show FAB when TOC exists; also show after scroll threshold (so it stays visible when scrolling)
        tocRoot.classList.add('toc-fab-visible');

        function setPanelOpen(open) {
            const isOpen = !!open;
            fab.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            panel.hidden = !isOpen;
            tocRoot.classList.toggle('toc-panel-open', isOpen);
            if (isOpen) {
                const firstLink = tocList.querySelector('.toc-link');
                if (firstLink) {
                    firstLink.focus();
                }
            } else {
                fab.focus();
            }
        }

        fab.addEventListener('click', function () {
            setPanelOpen(panel.hidden);
        });

        closeBtn.addEventListener('click', function () {
            setPanelOpen(false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) {
                setPanelOpen(false);
            }
        });

        // Scroll threshold: show FAB after scrolling down (only add class, never remove — keeps FAB discoverable)
        let ticking = false;
        function updateFabVisible() {
            if (window.pageYOffset > SCROLL_THRESHOLD) {
                tocRoot.classList.add('toc-fab-visible');
            }
        }
        function onScrollThreshold() {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateFabVisible();
                    ticking = false;
                });
                ticking = true;
            }
        }
        window.addEventListener('scroll', onScrollThreshold, { passive: true });
        updateFabVisible();

        highlightActiveTOCItem(headings, tocList);
    }

    function highlightActiveTOCItem(headings, tocList) {
        const tocItems = tocList.querySelectorAll('.toc-item');

        function updateActiveItem() {
            let current = '';
            const windowHeight = window.innerHeight;
            const scrollPosition = window.pageYOffset + (windowHeight / 3);

            headings.forEach((heading) => {
                const headingTop = heading.getBoundingClientRect().top + window.pageYOffset;
                if (scrollPosition >= headingTop) {
                    current = heading.id;
                }
            });

            tocItems.forEach((item) => {
                item.classList.remove('active');
            });

            if (current) {
                const activeLink = Array.from(tocList.querySelectorAll('a')).find(function (link) {
                    return link.getAttribute('href') === '#' + encodeURIComponent(current);
                });
                if (activeLink) {
                    activeLink.parentElement.classList.add('active');
                }
            }
        }

        let ticking = false;
        function onScroll() {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateActiveItem();
                    ticking = false;
                });
                ticking = true;
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        updateActiveItem();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', generateTableOfContents);
    } else {
        generateTableOfContents();
    }
})();


/**
 * Reading Progress Bar
 *
 * @package piratez_cyberpunk
 */

(function() {
    'use strict';

    function initReadingProgress() {
        const progressBar = document.querySelector('.reading-progress-bar');
        if (!progressBar) {
            return;
        }

        const article = (document.querySelector('.pz-editorial-content') || document.querySelector('.entry-content, .wp-block-post-content')) || document.querySelector('.site-main');
        if (!article) {
            return;
        }

        let lastProgress = 0;

        // Two-rAF: read dimensions/scroll in frame 1, write width in frame 2 to avoid forced reflow.
        function writeProgressState() {
            progressBar.style.width = lastProgress + '%';
            progressBar.setAttribute('aria-valuenow', String(Math.round(lastProgress)));
            ticking = false;
        }

        function readProgress() {
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const articleTop = article.getBoundingClientRect().top + scrollTop;
            const articleHeight = article.offsetHeight;

            let progress = 0;
            if (scrollTop >= articleTop) {
                const scrolled = scrollTop - articleTop;
                const maxScroll = articleHeight - windowHeight;
                progress = maxScroll > 0 ? Math.min((scrolled / maxScroll) * 100, 100) : 100;
            }
            lastProgress = progress;
            window.requestAnimationFrame(writeProgressState);
        }

        let ticking = false;
        function onScroll() {
            if (!ticking) {
                window.requestAnimationFrame(readProgress);
                ticking = true;
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        window.addEventListener('pageshow', onScroll);
        if (window.ResizeObserver) { new ResizeObserver(onScroll).observe(article); }
        // Initial calculation (defer to next frame to avoid forced reflow in same tick as other inits)
        requestAnimationFrame(readProgress);
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReadingProgress);
    } else {
        initReadingProgress();
    }
})();


(function(){
'use strict';
function init(){
 document.querySelectorAll('.share-copy').forEach(button=>button.addEventListener('click',async()=>{const status=button.closest('.social-sharing')?.querySelector('[role=status]');try{await navigator.clipboard.writeText(button.dataset.url);if(status)status.textContent='Link copied.';}catch(e){if(status)status.textContent='Copy the page address from your browser.';}}));
 if(window.pzEditorial && pzEditorial.code)document.querySelectorAll('.pz-editorial-content pre.wp-block-code, .entry-content pre.wp-block-code, .pz-code pre').forEach(pre=>{
  if(pre.dataset.pzCode)return;pre.dataset.pzCode='1';const code=pre.querySelector('code');if(!code)return;
  const copy=document.createElement('button');copy.type='button';copy.textContent='Copy code';copy.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(code.textContent);copy.textContent='Copied';}catch(e){copy.textContent='Select code to copy';}});pre.after(copy);
  // Token styling uses text nodes; never interpret code as HTML.
  const source=code.textContent;
  const token=/(#[^\n]*|\/\/[^\n]*|"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*'|\b(?:const|let|var|function|return|if|else|for|while|class|def|import|from|SELECT|FROM|WHERE|true|false|null)\b)/g;
  let offset=0;const fragment=document.createDocumentFragment();for(const match of source.matchAll(token)){fragment.append(document.createTextNode(source.slice(offset,match.index)));const span=document.createElement('span');span.className='pz-code-token';span.textContent=match[0];fragment.append(span);offset=match.index+match[0].length;}fragment.append(document.createTextNode(source.slice(offset)));code.replaceChildren(fragment);
  const language=(code.className.match(/language-([a-z0-9-]+)/i)||[])[1];if(language){const label=document.createElement('span');label.className='pz-code-language';label.textContent=language;pre.before(label);}
 });
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
