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

    // ── Copy button ──
    function doCopy(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        // Fallback (HTTP of oudere browser)
        var $tmp = $('<textarea>').css({ position: 'fixed', top: 0, left: 0, opacity: 0 })
                      .val(text).appendTo('body');
        $tmp[0].select();
        document.execCommand('copy');
        $tmp.remove();
        return Promise.resolve();
    }

    $(document).on('click', '.pl-copy-btn', function () {
        var $btn   = $(this);
        var id     = $btn.attr('data-id');
        var text   = ($btn.closest('.pl-card').find('.pl-prompt-raw').val() || '').trim();
        var $label = $btn.find('.pl-copy-label');

        if (!text) return;

        doCopy(text).then(function () {
            $label.text(plData.strings.copied);
            $btn.addClass('pl-copied');
            setTimeout(function () {
                $label.text(plData.strings.copy);
                $btn.removeClass('pl-copied');
            }, 2000);

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
            alert('Kopiëren mislukt. Selecteer de tekst handmatig.');
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

    // ── Init ──
    trackViews();

})(jQuery);
