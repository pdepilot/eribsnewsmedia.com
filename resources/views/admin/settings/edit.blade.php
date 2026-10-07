@extends('layouts.admin')

@section('title', 'Setup')

@section('stage')
    <header class="stage-head">
        <div class="head-top">
            <div>
                <p class="eyebrow">Newsroom · The house keys</p>
                <h1 class="head-title">Setup</h1>
                <p class="head-copy">The name on the mast, the login for this desk, and who is allowed through the door.</p>
            </div>
            <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Desk open</div>
        </div>
            <div class="metric-rail setup-metrics">
                <div class="metric">
                    <span class="label">Mast</span>
                    <span class="value is-text">{{ $settings['site_name'] ?? 'ERIBS' }}</span>
                </div>
                <div class="metric">
                    <span class="label">Signed in</span>
                    <span class="value is-text">{{ $account->name }}</span>
                </div>
                <div class="metric">
                    <span class="label">People</span>
                    <span class="value">{{ $users->count() }}</span>
                </div>
                <div class="metric">
                    <span class="label">Roles</span>
                    <span class="value">{{ $roles->count() }}</span>
                </div>
            </div>
    </header>
@endsection

@section('content')
        <div class="setup-board">
            <form method="post" action="{{ route('admin.settings.update') }}" class="setup-sheet" data-stamp="Mast">
                @csrf @method('PUT')
                <input type="hidden" name="seo_form" value="0">
                <div class="panel-title">
                    <div>
                        <h2>The mast</h2>
                        <p>Name, contact, and the channels on the site.</p>
                    </div>
                </div>
                <div class="setup-fields">
                    <label class="setup-field">Site name <input name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required></label>
                    <label class="setup-field">Tagline <input name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}"></label>
                    <label class="setup-field">Copyright <input name="copyright" value="{{ old('copyright', $settings['copyright'] ?? '') }}"></label>
                    <label class="setup-field">Contact email <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}"></label>
                    @foreach (['social_facebook' => 'Facebook', 'social_x' => 'X', 'social_instagram' => 'Instagram', 'social_youtube' => 'YouTube', 'social_pinterest' => 'Pinterest'] as $key => $label)
                        <label class="setup-field">{{ $label }} <input name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" placeholder="https://"></label>
                    @endforeach
                </div>
                <button class="setup-save">Save site details</button>
            </form>

            <form method="post" action="{{ route('admin.settings.account') }}" class="setup-key">
                @csrf @method('PUT')
                <p class="setup-kicker">Portal login</p>
                <h2>The key</h2>
                <p class="setup-key-copy">Change the name, email, and password for the account you are signed in with. Enter the current password to confirm.</p>
                <label class="setup-field">Name <input name="name" value="{{ old('name', $account->name) }}" required></label>
                <label class="setup-field">Email <input type="email" name="email" value="{{ old('email', $account->email) }}" required></label>
                <label class="setup-field">Current password
                    <span class="setup-secret">
                        <input id="current-password" type="password" name="current_password" required autocomplete="current-password">
                        <button type="button" data-toggle-pass aria-controls="current-password" aria-label="Show password" aria-pressed="false">Show</button>
                    </span>
                </label>
                <label class="setup-field">New password
                    <span class="setup-secret">
                        <input id="new-password" type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep it">
                        <button type="button" data-toggle-pass aria-controls="new-password" aria-label="Show password" aria-pressed="false">Show</button>
                    </span>
                </label>
                <label class="setup-field">Confirm new password <input id="confirm-password" type="password" name="password_confirmation" autocomplete="new-password"></label>
                <button class="setup-save is-light">Update login details</button>
            </form>

            <form method="post" action="{{ route('admin.users.roles') }}" class="setup-sheet setup-span" data-stamp="Desk">
                @csrf @method('PUT')
                <div class="panel-title">
                    <div>
                        <h2>Assign roles</h2>
                        <p>Each person keeps one role. The desk must keep at least one administrator.</p>
                    </div>
                    <button class="setup-save">Save role assignments</button>
                </div>
                <div class="setup-people">
                    @foreach ($users as $user)
                        <label class="setup-person">
                            <span class="setup-initial" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span>
                                <strong>{{ $user->name }}</strong>
                                <em>{{ $user->email }}</em>
                            </span>
                            <select name="roles[{{ $user->id }}]">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) old('roles.'.$user->id, $user->roles->first()?->id) === (string) $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
            </form>

            <section class="setup-permits">
                <div class="panel-title">
                    <div>
                        <h2>Permissions</h2>
                        <p>What each role may do once they are inside the portal.</p>
                    </div>
                </div>
                <div class="setup-role-grid">
                    @foreach ($roles as $role)
                        <form method="post" action="{{ route('admin.roles.update', $role) }}" class="setup-sheet" data-stamp="{{ strtoupper(substr($role->slug, 0, 4)) }}">
                            @csrf @method('PUT')
                            <div class="panel-title">
                                <div>
                                    <h2>{{ $role->name }}</h2>
                                    @if ($role->slug === 'admin')
                                        <p>Administrators keep every permission.</p>
                                    @else
                                        <p>{{ $role->permissions->count() }} of {{ $permissions->count() }} selected</p>
                                    @endif
                                </div>
                            </div>
                            <div class="setup-checks">
                                @foreach ($permissions as $permission)
                                    <label class="setup-check {{ $role->slug === 'admin' ? 'is-locked' : '' }}">
                                        @if ($role->slug === 'admin')
                                            <input type="checkbox" checked disabled>
                                        @else
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                                        @endif
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                            @if ($role->slug !== 'admin')
                                <button class="setup-save">Save {{ $role->name }} permissions</button>
                            @endif
                        </form>
                    @endforeach
                </div>
            </section>
        </div>
        <script>
            document.querySelectorAll('[data-toggle-pass]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.getAttribute('aria-controls'));
                    if (!input) return;
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    button.textContent = show ? 'Hide' : 'Show';
                    button.setAttribute('aria-pressed', show ? 'true' : 'false');
                    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                });
            });
        </script>
@endsection
