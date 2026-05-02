/* Prompt Library — Frontend scripts */
(function ($) {
    'use strict';

    if (typeof plData === 'undefined') return;

    var state = {
        search:   '',
        category: '',
        page:     1,
        maxPages: parseInt($('#pl-pagination').data('max-pages'), 10) || 1,
        loading:  false,
    };

    // ── Track views for visible cards (once per session via localStorage) ──
    function trackViews() {
        $('.pl-card').each(function () {
            var id  = $(this).data('id');
            var key = 'pl_viewed_' + id;
            var ts  = localStorage.getItem(key);
            var now = Date.now();

            if (!ts || now - parseInt(ts, 10) > 86400000) {
                localStorage.setItem(key, now);
                $.post(plData.ajaxUrl, {
                    action: 'pl_track_view',
                    nonce:  plData.nonce,
                    id:     id,
                });
            }
        });
    }

    // ── Copy helpers ──
    function doCopy(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        var $tmp = $('<textarea>').css({ position: 'absolute', left: '-9999px', top: '0' }).val(text).appendTo('body');
        $tmp[0].select();
        try { document.execCommand('copy'); } catch(e) {}
        $tmp.remove();
        return Promise.resolve();
    }

    function showCopied($btn, $label) {
        $label.text(plData.strings.copied);
        $btn.addClass('pl-copied');
        setTimeout(function () {
            $label.text(plData.strings.copy);
            $btn.removeClass('pl-copied');
        }, 2000);
    }

    // ── Copy button — leest prompt via AJAX rechtstreeks uit database ──
    $(document).on('click', '.pl-copy-btn', function () {
        var $btn   = $(this);
        var id     = $btn.attr('data-id');
        var $label = $btn.find('.pl-copy-label');

        if ($btn.hasClass('pl-loading')) return;
        $btn.addClass('pl-loading');
        $label.text('...');

        $.post(plData.ajaxUrl, {
            action: 'pl_get_prompt',
            nonce:  plData.nonce,
            id:     id,
        }, function (res) {
            $btn.removeClass('pl-loading');
            $label.text(plData.strings.copy);

            if (!res.success || !res.data.text) return;

            doCopy(res.data.text).then(function () {
                showCopied($btn, $label);
                $.post(plData.ajaxUrl, {
                    action: 'pl_track_copy',
                    nonce:  plData.nonce,
                    id:     id,
                }, function (res) {
                    if (res.success) {
                        $btn.closest('.pl-card').find('.pl-copies-count').text(res.data.copies);
                    }
                });
            }).catch(function () {
                alert('Kopiëren mislukt.');
            });
        });
    });

    // ── Like button ──
    $(document).on('click', '.pl-like-btn', function () {
        if (!plData.isLoggedIn) {
            alert(plData.strings.loginToLike);
            return;
        }
        var $btn = $(this);
        var id   = $btn.data('id');

        $.post(plData.ajaxUrl, {
            action: 'pl_toggle_like',
            nonce:  plData.nonce,
            id:     id,
        }, function (res) {
            if (res.success) {
                var $card = $btn.closest('.pl-card');
                $btn.toggleClass('pl-liked', res.data.liked);
                $card.find('.pl-likes-count').text(res.data.count);
            }
        });
    });

    // ── Load prompts via AJAX ──
    function loadPrompts(append) {
        if (state.loading) return;
        state.loading = true;

        var $grid = $('#pl-grid');
        var $btn  = $('#pl-load-more');

        if (!append) {
            $grid.addClass('pl-loading');
        } else {
            $btn.prop('disabled', true).text('...');
        }

        $.post(plData.ajaxUrl, {
            action:   'pl_load_prompts',
            nonce:    plData.nonce,
            search:   state.search,
            category: state.category,
            page:     state.page,
        }, function (res) {
            state.loading = false;
            $grid.removeClass('pl-loading');

            if (!res.success) return;

            if (append) {
                $grid.append(res.data.html);
            } else {
                $grid.html(res.data.html);
            }

            state.maxPages = res.data.max_pages || 1;

            var $pagination = $('#pl-pagination');
            $pagination.data('max-pages', state.maxPages);

            if (state.page >= state.maxPages) {
                $btn.hide();
            } else {
                $btn.prop('disabled', false).text('Meer prompts laden').show();
            }

            trackViews();
        });
    }

    // ── Search (debounced) ──
    var searchTimer;
    $(document).on('input', '#pl-search', function () {
        clearTimeout(searchTimer);
        var val = $(this).val();
        searchTimer = setTimeout(function () {
            state.search = val;
            state.page   = 1;
            loadPrompts(false);
        }, 400);
    });

    // ── Category filter ──
    $(document).on('click', '.pl-cat-btn', function () {
        $('.pl-cat-btn').removeClass('active');
        $(this).addClass('active');
        state.category = $(this).data('cat');
        state.page     = 1;
        loadPrompts(false);
    });

    // ── Load more ──
    $(document).on('click', '#pl-load-more', function () {
        state.page++;
        loadPrompts(true);
    });

    // ── Vul prompt-boxen via AJAX (werkt ook bij pagina-cache) ──
    function populatePromptBoxes() {
        $('.pl-prompt-box').each(function () {
            var $box     = $(this);
            var $preview = $box.find('.pl-prompt-preview');
            if ($preview.text().trim()) return; // al ingevuld door PHP
            var id = $box.data('post-id');
            if (!id) return;

            $.post(plData.ajaxUrl, {
                action: 'pl_get_prompt',
                nonce:  plData.nonce,
                id:     id,
            }, function (res) {
                if (res.success && res.data.text) {
                    $preview.text(res.data.text);
                }
            });
        });
    }

    // ── Init ──
    trackViews();
    populatePromptBoxes();

})(jQuery);
