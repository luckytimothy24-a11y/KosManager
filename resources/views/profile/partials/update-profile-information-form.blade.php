<form method="post" action="{{ route('profile.update') }}" class="space-y-5 max-w-xl">
    @csrf
    @method('patch')

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Nama lengkap Anda" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="anda@email.com" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-3 flex items-start gap-2 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20 px-3.5 py-2.5">
                <i class="ri-mail-warning-line text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0"></i>
                <p class="text-xs leading-relaxed text-yellow-800 dark:text-yellow-300">
                    Alamat email Anda belum terverifikasi.
                    <button form="send-verification" type="submit" class="underline underline-offset-2 font-semibold hover:text-yellow-900 dark:hover:text-yellow-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 rounded">
                        Kirim ulang email verifikasi
                    </button>
                </p>
            </div>

            @if (session('status') === 'verification-link-sent')
                <p class="mt-2 flex items-center gap-1.5 font-medium text-sm text-green-600 dark:text-green-400">
                    <i class="ri-checkbox-circle-line"></i> Link verifikasi baru telah dikirim ke alamat email Anda.
                </p>
            @endif
        @endif
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1.5 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" placeholder="08xxxxxxxxxx" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="address" :value="__('Address')" />
        <x-text-input id="address" name="address" type="text" class="mt-1.5 block w-full" :value="old('address', $user->address)" autocomplete="street-address" placeholder="Alamat tempat tinggal" />
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>

    <div>
        <x-input-label for="identity_number" :value="__('Identity Number (NIK)')" />
        <x-text-input id="identity_number" name="identity_number" type="text" class="mt-1.5 block w-full" :value="old('identity_number', $user->identity_number)" inputmode="numeric" maxlength="50" placeholder="16 digit NIK" />
        <x-input-error class="mt-2" :messages="$errors->get('identity_number')" />
        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Nomor identitas hanya terlihat oleh Anda dan tidak dibagikan ke pengguna lain.</p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    <div class="flex items-center gap-4 pt-1">
        <x-primary-button>
            <i class="ri-save-line"></i> Simpan Perubahan
        </x-primary-button>

        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 5000)"
                role="status"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 dark:text-green-400"
            ><i class="ri-checkbox-circle-line"></i> Berhasil disimpan</p>
        @endif
    </div>
</form>
