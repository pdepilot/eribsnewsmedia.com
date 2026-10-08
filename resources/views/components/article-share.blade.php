@props(['article'])
@php
    $siteName = app(\App\Services\SiteContext::class)->data()['siteName'];
    $url = $article->canonicalUrl();
    $message = 'Check out this story from '.$siteName.': '.$article->title.' '.$url;
    $mobile = preg_match('/Android|iPhone|iPad|Mobile/i', (string) request()->userAgent()) === 1;
    $whatsApp = ($mobile ? 'https://api.whatsapp.com/send?text=' : 'https://web.whatsapp.com/send?text=').rawurlencode($message);
    $facebook = 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($url);
    $x = 'https://twitter.com/intent/tweet?url='.rawurlencode($url).'&text='.rawurlencode($article->title);
@endphp
<div class="share" data-article-share>
    <a class="share-link" href="{{ $whatsApp }}" rel="noopener noreferrer" target="_blank">WhatsApp</a>
    <a class="share-link" href="{{ $facebook }}" rel="noopener noreferrer" target="_blank">Facebook</a>
    <a class="share-link" href="{{ $x }}" rel="noopener noreferrer" target="_blank">X</a>
    <button type="button" class="share-link" data-copy-link data-url="{{ $url }}">Copy link</button>
    <button type="button" class="share-link share-native" data-native-share hidden>Share</button>
    <span class="share-note" data-copy-note hidden>Link copied</span>
</div>
<script>
    document.querySelectorAll('[data-article-share]').forEach(function (share) {
        if (share.dataset.shareReady === '1') return;
        share.dataset.shareReady = '1';
        var button = share.querySelector('[data-copy-link]');
        var note = share.querySelector('[data-copy-note]');
        var native = share.querySelector('[data-native-share]');
        var url = button ? button.getAttribute('data-url') || '' : '';
        var copied = function () {
            if (!button) return;
            button.textContent = 'Link copied';
            if (note) note.hidden = false;
        };
        var fallback = function () {
            var input = document.createElement('textarea');
            input.value = url;
            input.setAttribute('readonly', '');
            input.style.position = 'fixed';
            input.style.left = '-9999px';
            document.body.appendChild(input);
            input.select();
            try { document.execCommand('copy'); } catch (error) {}
            document.body.removeChild(input);
            copied();
        };
        if (button) {
            button.addEventListener('click', function () {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(copied).catch(fallback);
                } else {
                    fallback();
                }
            });
        }
        if (native && navigator.share) {
            native.hidden = false;
            native.addEventListener('click', function () {
                navigator.share({ title: document.title, text: @json($message), url: url }).catch(function () {});
            });
        }
    });
</script>
