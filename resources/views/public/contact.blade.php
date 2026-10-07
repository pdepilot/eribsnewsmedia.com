@extends('layouts.public')

@section('meta')
    <x-seo title="Contact Us" :canonical="route('pages.contact')" />
@endsection

@section('content')
    <div class="container contact-desk">
        <header class="contact-intro">
            <p class="kicker">Lagos newsroom</p>
            <h1 class="contact-title">Write to the desk</h1>
            <p>A letter to {{ $siteName }} arrives on this page. The newsroom reads it from the portal.</p>
        </header>

        <div class="contact-board">
            <aside class="contact-mark">
                <p class="contact-place">Lagos</p>
                <p class="contact-when"><time datetime="{{ now()->toDateString() }}">{{ now()->format('l, j F Y') }}</time></p>
                @if (filled($deskEmail))
                    <a class="contact-mail" href="mailto:{{ $deskEmail }}">{{ $deskEmail }}</a>
                @else
                    <p class="contact-note">No public address is set yet. Use the letter.</p>
                @endif
            </aside>

            <form class="contact-letter" method="post" action="{{ route('contact.store') }}">
                @csrf
                @if ($errors->any())
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                </div>
                <div class="field">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required>
                </div>
                <div class="field">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="7" required>{{ old('message') }}</textarea>
                </div>
                <button class="contact-send" type="submit">Send the letter</button>
            </form>
        </div>
    </div>
@endsection
