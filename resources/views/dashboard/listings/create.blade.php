@extends('layouts.app')

@section('title', 'Naujas skelbimas - ieškau.lt')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold mb-6">Naujas skelbimas</h1>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboard.listings.store') }}" method="POST" enctype="multipart/form-data"
              x-data="listingForm()"
              x-init="initForm({{ json_encode($attributesByCategory) }})">
            @csrf

            <!-- Pagrindinė informacija -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Pagrindinė informacija</h2>

                <!-- Pavadinimas -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Pavadinimas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Pvz., Toyota Avensis 2.0 D-4D">
                </div>

                <!-- Kategorija -->
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategorija <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                            x-model="selectedCategory"
                            @change="onCategoryChange()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pasirinkite kategoriją</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Subkategorija -->
                <div class="mb-4" x-show="subcategories.length > 0" style="display: none;">
                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Subkategorija
                    </label>
                    <select name="subcategory_id" id="subcategory_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pasirinkite subkategoriją</option>
                        <template x-for="sub in subcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Skelbimo tipas -->
                <div class="mb-4">
                    <label for="listing_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Skelbimo tipas <span class="text-red-500">*</span>
                    </label>
                    <select name="listing_type_id" id="listing_type_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pasirinkite tipą</option>
                        @foreach($listingTypes as $type)
                            <option value="{{ $type->id }}" {{ old('listing_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Aprašymas -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Aprašymas <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Detalus skelbimo aprašymas...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Kaina ir vieta -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Kaina ir vieta</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kaina -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                            Kaina (€)
                        </label>
                        <input type="number" name="price" id="price" step="0.01" min="0"
                               value="{{ old('price') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>

                    <!-- Kainos tipas -->
                    <div>
                        <label for="price_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Kainos tipas <span class="text-red-500">*</span>
                        </label>
                        <select name="price_type" id="price_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="fixed" {{ old('price_type') == 'fixed' ? 'selected' : '' }}>Fiksuota</option>
                            <option value="negotiable" {{ old('price_type') == 'negotiable' ? 'selected' : '' }}>Derinama</option>
                            <option value="free" {{ old('price_type') == 'free' ? 'selected' : '' }}>Nemokamai</option>
                            <option value="on_request" {{ old('price_type') == 'on_request' ? 'selected' : '' }}>Susitarti</option>
                        </select>
                    </div>
                </div>

                <!-- Lokacija -->
                <div class="mt-4">
                    <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Miestas <span class="text-red-500">*</span>
                    </label>
                    <select name="location_id" id="location_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pasirinkite miestą</option>
                        @foreach($locations as $location)
                            @if($location->type === 'city')
                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Dinamiški atributai -->
            <div x-show="currentAttributes.length > 0" style="display: none;" class="mb-6">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Papildoma informacija</h2>

                <template x-for="attr in currentAttributes" :key="attr.id">
                    <div class="mb-4">
                        <label :for="'attr_' + attr.id" class="block text-sm font-medium text-gray-700 mb-1">
                            <span x-text="attr.name"></span>
                            <span x-show="attr.is_required" class="text-red-500">*</span>
                        </label>

                        <!-- Text input -->
                        <template x-if="attr.type === 'text'">
                            <input type="text"
                                   :name="'attributes[' + attr.id + ']'"
                                   :id="'attr_' + attr.id"
                                   :required="attr.is_required"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </template>

                        <!-- Number input -->
                        <template x-if="attr.type === 'number'">
                            <input type="number"
                                   :name="'attributes[' + attr.id + ']'"
                                   :id="'attr_' + attr.id"
                                   :required="attr.is_required"
                                   step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </template>

                        <!-- Textarea -->
                        <template x-if="attr.type === 'textarea'">
                            <textarea :name="'attributes[' + attr.id + ']'"
                                      :id="'attr_' + attr.id"
                                      :required="attr.is_required"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </template>

                        <!-- Select -->
                        <template x-if="attr.type === 'select'">
                            <select :name="'attributes[' + attr.id + ']'"
                                    :id="'attr_' + attr.id"
                                    :required="attr.is_required"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pasirinkite...</option>
                                <template x-for="option in attr.options" :key="option.id">
                                    <option :value="option.id" x-text="option.value"></option>
                                </template>
                            </select>
                        </template>

                        <!-- Multiselect -->
                        <template x-if="attr.type === 'multiselect'">
                            <div class="space-y-2">
                                <template x-for="option in attr.options" :key="option.id">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               :name="'attributes[' + attr.id + '][]'"
                                               :value="option.id"
                                               class="mr-2">
                                        <span x-text="option.value"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <!-- Checkbox -->
                        <template x-if="attr.type === 'checkbox'">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       :name="'attributes[' + attr.id + ']'"
                                       value="1"
                                       class="mr-2">
                                <span x-text="attr.name"></span>
                            </label>
                        </template>

                        <!-- Date -->
                        <template x-if="attr.type === 'date'">
                            <input type="date"
                                   :name="'attributes[' + attr.id + ']'"
                                   :id="'attr_' + attr.id"
                                   :required="attr.is_required"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </template>
                    </div>
                </template>
            </div>

            <!-- Nuotraukos -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Nuotraukos</h2>
                <div class="mb-4">
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-1">
                        Įkelti nuotraukas (max 10)
                    </label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Galite įkelti iki 10 nuotraukų (JPG, PNG, max 5MB kiekviena)</p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('dashboard.listings') }}"
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Atšaukti
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Paskelbti
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function listingForm() {
    return {
        selectedCategory: '',
        subcategories: [],
        currentAttributes: [],
        allAttributes: {},
        allSubcategories: @json($subcategories->groupBy('category_id')),

        initForm(attributesByCategory) {
            this.allAttributes = attributesByCategory;
        },

        onCategoryChange() {
            // Load subcategories
            this.subcategories = this.allSubcategories[this.selectedCategory] || [];

            // Load attributes
            this.currentAttributes = this.allAttributes[this.selectedCategory] || [];
        }
    }
}
</script>
@endsection
