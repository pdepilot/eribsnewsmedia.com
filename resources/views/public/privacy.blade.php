@extends('layouts.public')

@section('meta')
    <x-seo title="Privacy Policy" :canonical="route('pages.privacy')" />
@endsection

@section('content')
    <div class="container policy-desk">
        <p class="crumbs"><a href="{{ route('home') }}">Home</a> <span aria-hidden="true">»</span> Privacy Policy</p>

        <header class="policy-intro">
            <p class="kicker">Lagos newsroom</p>
            <h1 class="policy-title">Privacy Policy</h1>
            <p class="policy-by">by {{ $siteName }} <time datetime="2026-10-06">6 October 2026</time></p>
        </header>

        <article class="policy-sheet">
            <p>{{ $siteName }} collects information at a few points on this site: a comment, a letter to the desk, and the ordinary record of a visit. The newsroom keeps that information. It is not sold, and it is not handed to another organisation except where this policy says so.</p>

            <h2>Comments</h2>
            <p>Anyone can comment on a published story. The form asks for a name, an email address, and the comment. The name and the comment appear under the story. The email stays with the newsroom and is used to screen comments that break the terms of use. It is not passed to another organisation.</p>

            <h2>Newsletters</h2>
            <p>This site does not offer an email newsletter. There is no mailing list to join, and no newsletter cookie is set.</p>

            <h2>Letters to the desk</h2>
            <p>The contact page asks for a name, an email address, a subject, and a message. Those letters are stored and read in the newsroom portal. They are kept so the desk can reply. They are not passed to another organisation.</p>

            <h2>Log files</h2>
            <p>A visit can leave a short technical record. The session store may keep an IP address, the browser’s user agent, and the time of the last activity. Opening a story also records a view, using a one-way hash of the session identifier rather than a name. These records are used to run the site and to count which stories are read. They are not published beside a comment or a letter.</p>

            <h2>Legal disclaimer</h2>
            <p>The newsroom works to keep this information private. It may still disclose personal information when the law requires it, including a court order or other lawful process, where there is a good-faith belief that the disclosure is necessary.</p>

            <h2>Business transitions</h2>
            <p>If the newsroom is transferred to another publisher, merged, or sold, the letters, comments, and technical records described here may pass with it.</p>

            <h2>Links</h2>
            <p>Stories and the masthead link to other websites, including social networks. {{ $siteName }} is not responsible for the privacy practices or the content of those sites. This policy covers only information collected here.</p>

            <h2>Third party advertising</h2>
            <p>When the newsroom switches an advertisement on, that unit may be an image or code from an advertising service, including Google AdSense. The service can tell that a page was viewed and how often an ad was shown. It does not receive the name or email typed into a comment or a letter. While a unit is on, the service may set its own cookie. Google describes those cookies in its <a href="https://policies.google.com/technologies/ads" rel="noopener">advertising privacy notice</a>.</p>

            <h2>Cookie policy</h2>

            <h3>What are cookies</h3>
            <p>Cookies are small text files a site stores on a browser so it can recognise that browser on a later request.</p>

            <h3>How we use cookies</h3>
            <p>This site uses cookies to keep a visit together, to protect forms, and to count a story view once. When the newsroom has switched them on, cookies also load analytics or advertising from another service. Turning every cookie off in the browser can stop comments, letters, and the portal sign-in from working.</p>

            <h3>Disabling cookies</h3>
            <p>Cookies can be blocked in the browser’s own settings. Blocking them also limits this site: a form that needs a session will fail, and a signed-in newsroom session will not stay signed in.</p>

            <h3>The cookies we set</h3>
            <ul>
                <li><strong>Session cookie.</strong> The site sets a session cookie so one visit stays in one piece. It keeps a newsroom user signed in, remembers which stories were already counted, and carries the form token. It is sent only to this site.</li>
                <li><strong>Form token.</strong> A second cookie, commonly named XSRF-TOKEN, lets the comment form, the contact form, and the portal sign-in submit safely. It does not store a name or a letter.</li>
                <li><strong>No newsletter or preference cookie.</strong> The site does not remember a mailing-list choice, and it does not store a setting for how the pages should look.</li>
            </ul>

            <h3>Third party cookies</h3>
            <ul>
                <li>
                    <strong>Google Analytics.</strong>
                    @if ($analyticsOn)
                        Analytics is on. The site loads Google’s tag, and Google may set cookies that record how long a browser stays and which pages it opens.
                    @else
                        Analytics is off. If the newsroom turns it on, the site will load Google’s tag, and Google may set cookies that record how long a browser stays and which pages it opens.
                    @endif
                    Google describes those cookies in its <a href="https://policies.google.com/technologies/cookies" rel="noopener">cookie policy</a>.
                </li>
                <li><strong>Advertising.</strong> An active advertisement, including a Google AdSense unit, may set an advertising cookie to limit how often an ad is shown and to choose an ad. Google’s notice is the same <a href="https://policies.google.com/technologies/ads" rel="noopener">advertising privacy page</a>.</li>
                <li><strong>Share links and social links.</strong> A story, and this page, can open Facebook or X in a new tab. The masthead can also link to Facebook, X, Instagram, YouTube, or Pinterest when those addresses are set. Those are ordinary links, not plugins embedded in the page. The other site applies its own policy after you arrive.</li>
            </ul>

            <h2>Contact</h2>
            <p>Questions about this policy can go to the desk.</p>
            @if (filled($deskEmail))
                <p><a class="policy-mail" href="mailto:{{ $deskEmail }}">{{ $deskEmail }}</a></p>
            @else
                <p><a class="policy-mail" href="{{ route('pages.contact') }}">Write to the desk</a></p>
            @endif

            @if (filled($note))
                <h2>Newsroom note</h2>
                <div class="policy-note">{{ $note }}</div>
            @endif
        </article>

        <div class="share">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('pages.privacy')) }}&text={{ urlencode('Privacy Policy') }}" rel="noopener" target="_blank">Share on X</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('pages.privacy')) }}" rel="noopener" target="_blank">Share on Facebook</a>
        </div>
    </div>
@endsection
