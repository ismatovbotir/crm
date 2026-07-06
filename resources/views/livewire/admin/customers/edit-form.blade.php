<form wire:submit="save" class="space-y-4">

    {{-- Company identifiers --}}
    <x-input
        label="Название компании"
        wire:model="name"
        :error="$errors->first('name')"
        :required="true"
        placeholder="Магазин «Восток»"
    />

    <x-input
        label="Юридическое название"
        wire:model="legal_name"
        :error="$errors->first('legal_name')"
        placeholder="ООО «Восток Трейд»"
        hint="Полное юридическое наименование организации"
    />

    <div class="grid grid-cols-2 gap-4">
        <x-input
            label="ИНН"
            wire:model="inn"
            :error="$errors->first('inn')"
            placeholder="123456789"
        />
        <x-input
            label="ОКЭД"
            wire:model="oked"
            :error="$errors->first('oked')"
            placeholder="47110"
        />
    </div>

    {{-- Classification --}}
    <div class="grid grid-cols-2 gap-4">
        <x-select
            label="Тип бизнеса"
            wire:model="business_type_id"
            :error="$errors->first('business_type_id')"
        >
            <option value="">— выберите —</option>
            @foreach($businessTypes as $bt)
            <option value="{{ $bt->id }}">{{ $bt->name }}</option>
            @endforeach
        </x-select>

        <x-select
            label="Сегмент"
            wire:model="segment"
            :error="$errors->first('segment')"
        >
            <option value="A">A — крупный клиент</option>
            <option value="B">B — средний клиент</option>
            <option value="C">C — малый клиент</option>
        </x-select>
    </div>

    <x-select
        label="Статус"
        wire:model="status"
        :error="$errors->first('status')"
        :required="true"
    >
        <option value="active">Активный</option>
        <option value="vip">VIP</option>
        <option value="inactive">Неактивен</option>
        <option value="blocked">Заблокирован</option>
    </x-select>

    {{-- Contact info --}}
    <div class="pt-2 border-t border-gray-100">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Контактные данные</p>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-input
                    label="Телефон"
                    wire:model="phone"
                    :error="$errors->first('phone')"
                    placeholder="+998 71 123-45-67"
                />
                <x-input
                    label="Email"
                    type="email"
                    wire:model="email"
                    :error="$errors->first('email')"
                    placeholder="info@company.uz"
                />
            </div>
            <x-input
                label="Сайт"
                wire:model="website"
                :error="$errors->first('website')"
                placeholder="https://company.uz"
            />
        </div>
    </div>

    {{-- Location --}}
    <div class="pt-2 border-t border-gray-100">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Местоположение</p>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-select
                    label="Регион"
                    wire:model="region"
                    :error="$errors->first('region')"
                >
                    <option value="">— выберите —</option>
                    @foreach($regions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </x-select>
                <x-input
                    label="Город"
                    wire:model="city"
                    :error="$errors->first('city')"
                    placeholder="Ташкент"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Юридический адрес</label>
                <textarea
                    wire:model="address"
                    rows="2"
                    placeholder="ул. Навои, д. 15, офис 301"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                ></textarea>
            </div>
        </div>
    </div>

    {{-- Banking --}}
    <div class="pt-2 border-t border-gray-100">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Банковские реквизиты</p>
        <div class="grid grid-cols-2 gap-4">
            <x-select
                label="Банк"
                wire:model="bank_id"
                :error="$errors->first('bank_id')"
            >
                <option value="">— выберите —</option>
                @foreach($banks as $bank)
                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                @endforeach
            </x-select>
            <x-input
                label="Расчётный счёт"
                wire:model="bank_account"
                :error="$errors->first('bank_account')"
                placeholder="20 цифр"
                maxlength="20"
            />
        </div>
    </div>

    {{-- Commercial terms --}}
    <div class="pt-2 border-t border-gray-100">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Коммерческие условия</p>
        <div class="grid grid-cols-2 gap-4">
            <x-input
                label="Кредитный лимит (UZS)"
                type="number"
                wire:model="credit_limit"
                :error="$errors->first('credit_limit')"
                placeholder="0"
            />
            <x-input
                label="Отсрочка (дней)"
                type="number"
                wire:model="payment_terms_days"
                :error="$errors->first('payment_terms_days')"
                placeholder="0"
                min="0"
            />
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Заметки</label>
        <textarea
            wire:model="notes"
            rows="3"
            placeholder="Внутренние заметки о клиенте..."
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        ></textarea>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
        <x-button type="button" variant="secondary" wire:click="$parent.closeForm">
            Отмена
        </x-button>
        <x-button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Сохранить</span>
            <span wire:loading wire:target="save">Сохранение...</span>
        </x-button>
    </div>

</form>
