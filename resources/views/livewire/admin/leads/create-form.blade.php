<form id="lead-create-form" wire:submit="save" class="space-y-4">

    {{-- Contact info --}}
    <x-input
        label="Имя контакта"
        wire:model="name"
        :error="$errors->first('name')"
        :required="true"
        placeholder="Иван Иванов"
    />

    <x-input
        label="Компания"
        wire:model="company"
        :error="$errors->first('company')"
        placeholder="ООО Магнит"
    />

    <div class="grid grid-cols-2 gap-4">
        <x-input
            label="Телефон"
            wire:model="phone"
            :error="$errors->first('phone')"
            :required="true"
            placeholder="+998 90 123-45-67"
        />
        <x-input
            label="Email"
            type="email"
            wire:model="email"
            :error="$errors->first('email')"
            placeholder="ivan@example.com"
        />
    </div>

    {{-- Classification --}}
    <div class="grid grid-cols-2 gap-4">
        <x-select
            label="Статус"
            wire:model="status"
            :error="$errors->first('status')"
            :required="true"
        >
            @foreach($statuses as $s)
            <option value="{{ $s }}">{{ match($s) {
                'new'            => 'Новый',
                'qualified'      => 'Квалифицирован',
                'contacted'      => 'Контакт',
                'in_negotiation' => 'Переговоры',
                'won'            => 'Успех',
                'lost'           => 'Проигран',
                default          => $s
            } }}</option>
            @endforeach
        </x-select>

        <x-select
            label="Источник"
            wire:model="source_id"
            :error="$errors->first('source_id')"
            :required="true"
        >
            <option value="">— выберите —</option>
            @foreach($sources as $src)
            <option value="{{ $src->id }}">{{ $src->name }}</option>
            @endforeach
        </x-select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-select
            label="Менеджер"
            wire:model="manager_id"
            :error="$errors->first('manager_id')"
        >
            <option value="">— не назначен —</option>
            @foreach($managers as $m)
            <option value="{{ $m->id }}">{{ $m->name }}</option>
            @endforeach
        </x-select>

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
    </div>

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
            label="Оценка (1-10)"
            type="number"
            wire:model="score"
            :error="$errors->first('score')"
            min="1"
            max="10"
            placeholder="5"
        />
    </div>

    <x-input
        label="Бюджет (UZS)"
        type="number"
        wire:model="budget"
        :error="$errors->first('budget')"
        placeholder="5 000 000"
    />

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Заметки</label>
        <textarea
            wire:model="notes"
            rows="3"
            placeholder="Дополнительная информация..."
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
        ></textarea>
    </div>


</form>
