@extends('layouts.public')

@section('meta')
    <x-seo title="Terms of Use" :canonical="route('pages.terms')" />
@endsection

@section('content')
    <div class="container policy-desk">
        <p class="crumbs"><a href="{{ route('home') }}">Home</a> <span aria-hidden="true">»</span> Terms of Use</p>

        <header class="policy-intro">
            <p class="kicker">Lagos newsroom</p>
            <h1 class="policy-title">Terms of Use</h1>
            <p class="policy-by">by {{ $siteName }} <time datetime="2026-10-06">6 October 2026</time></p>
        </header>

        <article class="policy-sheet">
            <p>You may read {{ $siteName }}, comment on a story, write to the desk, and follow the RSS feed. Using the site means you agree to these terms. If you do not agree, do not use the site.</p>
            <p>The newsroom may change these terms and will publish the new version on this page. If you keep using the site after that change is published, you agree to the new version. The <a href="{{ route('pages.privacy') }}">privacy policy</a> explains what the site stores about a visit, a comment, or a letter.</p>

            <h2>Violations</h2>
            <p>If you believe something on the site breaks these terms, write to the desk. The newsroom does not promise that a letter will lead to a particular action.</p>
            @if (filled($deskEmail))
                <p><a class="policy-mail" href="mailto:{{ $deskEmail }}">{{ $deskEmail }}</a></p>
            @else
                <p><a class="policy-mail" href="{{ route('pages.contact') }}">Write to the desk</a></p>
            @endif

            <h2>Stories can be incomplete</h2>
            <p>Stories are published for general information. The newsroom reviews them before they go live, and a story can still be incomplete, later corrected, or written from a point of view. Comments are the words of the person who posted them. Do not treat a story or a comment as professional, legal, medical, or financial advice. Read with care.</p>

            <h2>Commenting rules</h2>
            <p>You are responsible for what you post. You use the site as it is, at your own risk. A comment should be truthful. Do not post any of the following:</p>
            <ul>
                <li>An offer to sell, trade, or exchange securities.</li>
                <li>Harassment, defamation, threats, stalking, bullying, or a violation of someone else’s legal rights.</li>
                <li>Anything that asks for or describes an illegal act as something to do.</li>
                <li>A pretence that you are another person, or a false claim that you represent an organisation.</li>
                <li>Someone else’s copyright, trademark, or trade secret.</li>
                <li>Obscene, hateful, or racially offensive language or images.</li>
                <li>An advertisement for a business.</li>
                <li>Gambling, a chain letter, a pyramid scheme, or a multi-level marketing pitch.</li>
                <li>Any other breach of the law that applies to your use of the site, including securities law.</li>
                <li>A personal attack on the author of a story.</li>
            </ul>
            <p>If someone says a comment you posted is unlawful, you carry the responsibility of showing that it is allowed.</p>

            <h2>Disclosure and removal</h2>
            <p>The newsroom may disclose information when a law, a regulation, a legal process, or a government request requires it. It may edit a comment, refuse it, or take it down, and it may block a visitor it believes is using the site in a way that breaks these terms. It may remove material that breaks these rules once it knows about it, and it is not obliged to watch every comment. If a page offends you, stop using the site.</p>

            <h2>Disclaimer</h2>
            <p>A comment is the opinion of the person who wrote it. It is not a statement of the newsroom. Stories are reviewed before publication. Comments appear when they are posted, and the newsroom may remove one afterwards. Links to other sites are not an endorsement of those sites.</p>
            <p>The site and its stories are provided as they are, without a warranty of any kind, including any implied warranty of merchantability, fitness for a particular purpose, or non-infringement. The newsroom does not warrant that the site will be uninterrupted, free of errors, or free of harmful code. You use what you read here at your own risk.</p>
            <p>{{ $siteName }} is not liable for loss or damage arising from your use of the site or your inability to use it, from a comment you posted, from an error or a gap in a story, or from your reliance on this site or on a site it links to. The newsroom is not an intermediary, broker, investment adviser, or exchange.</p>
            <p>In return for your agreement to these terms, you receive a personal, non-exclusive, non-transferable, revocable licence to read the site for your own non-commercial use. You decide whether a particular use of a story is permitted.</p>
            <p>Posting a comment gives the newsroom permission to publish it on the story, keep it, and remove it. You remain responsible for the words you wrote.</p>

            <h2>Copyright</h2>
            <p>If you believe a page uses your work without permission, write to the desk. Include the address of the page, a description of the work, and a way to reply. There is no separate infringement form.</p>

            <h2>Unauthorised use</h2>
            <p>Do not use the site to send unsolicited email, or to help someone else do so. That misuse can bring civil, criminal, or administrative penalties for the sender and for anyone assisting the sender.</p>

            <h2>Indemnity</h2>
            <p>You agree to cover the newsroom for claims, losses, damages, and legal fees that result from your breach of these terms, your use of the site, or a comment you posted, and to cooperate in the defence of such a claim.</p>

            <h2>Whole agreement</h2>
            <p>These terms are the whole agreement between you and {{ $siteName }} about the use of the site. They replace any earlier oral or written agreement on that subject.</p>

            @if (filled($note))
                <h2>Newsroom note</h2>
                <div class="policy-note">{{ $note }}</div>
            @endif
        </article>

        <div class="share">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('pages.terms')) }}&text={{ urlencode('Terms of Use') }}" rel="noopener" target="_blank">Share on X</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('pages.terms')) }}" rel="noopener" target="_blank">Share on Facebook</a>
        </div>
    </div>
@endsection
