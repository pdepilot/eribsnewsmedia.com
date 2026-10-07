@extends('layouts.auth')

@section('content')
    <main class="staff-stage">
        <section class="staff-gallery">
            <div class="staff-gallery-media"></div>
            <div class="staff-gallery-shade"></div>
            <header class="staff-top">
                <a href="{{ route('home') }}">Return to the site</a>
                <span class="staff-clock" data-clock>00:00:00 WAT</span>
                <span>Newsroom</span>
            </header>
            <p class="staff-spine">News desk · Report · Review · Publish</p>
            <div class="staff-gallery-copy">
                <p class="staff-kicker">ERIBS Media · Newsroom</p>
                <h1>The news desk.</h1>
                <div class="staff-rule"></div>
                <p class="staff-lede">For editors and reporters. Sign in to file, review, and publish the day's stories.</p>
                <div class="staff-meta">
                    <div>
                        <span>Seat</span>
                        <strong>Lagos</strong>
                    </div>
                    <div>
                        <span>Channel</span>
                        <strong>Newsroom session</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="staff-vault">
            <div class="staff-card">
                <div class="staff-mark">
                    <img src="{{ asset('images/logo.png').'?v=2' }}" alt="ERIBS Media" width="652" height="263">
                    <p>Newsroom desk</p>
                </div>
                <h1>Welcome back.</h1>
                <div class="staff-rule"></div>
                <p class="staff-note">Sign in with the email issued for this desk. Stories stay in draft until an editor publishes them.</p>
                @if ($errors->any())
                    <p class="staff-alert">{{ $errors->first() }}</p>
                @endif

                <form class="staff-form" method="post" action="{{ route('login') }}">
                    @csrf
                    <label class="staff-field">
                        <span>Email</span>
                        <div class="staff-input">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="you@eribs.test" required autofocus>
                        </div>
                    </label>
                    <label class="staff-field">
                        <span>Password</span>
                        <div class="staff-input">
                            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Your desk passphrase" required>
                            <button type="button" data-toggle-pass aria-controls="password" aria-label="Show password" aria-pressed="false">Show</button>
                        </div>
                    </label>
                    <div class="staff-row">
                        <label><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
                    </div>
                    <button class="staff-submit" type="submit">Enter the desk</button>
                </form>
                <p class="staff-foot">ERIBS Media · Lagos</p>
            </div>
        </section>
    </main>
@endsection
