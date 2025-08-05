<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Group;
use App\Models\Item;

class EventItemSelector extends Component
{
    public array $categories = [];
    public array $subcategories = [];
    public array $groups = [];
    public array $items = [];

    public $selectedCategory = null;
    public $selectedSubcategory = null;
    public $selectedGroup = null;
    public $selectedItem = null;
    public int $quantity = 1;

    public array $addedItems = [];

    public $availableQuantity;
    public $quantityWarning;

    public function mount(): void
    {
        $this->categories = Category::pluck('name', 'id')->toArray();
    }

// Validate the quantity against the available stock
        public function updatedSelectedItem($value): void
    {
        $item = Item::find($value);

        if ($item) {
            $this->availableQuantity = $item->quantity;
        } else {
            $this->availableQuantity = null;
        }

        $this->validateQuantity(); // Check if current quantity is still valid
    }

    // Validate the quantity when it is updated
    public function updatedQuantity($value): void
{
    $this->validateQuantity();
}
// Validate the quantity against the available stock
private function validateQuantity(): void
{
    if ($this->selectedItem && $this->availableQuantity !== null) {
        if ($this->quantity > $this->availableQuantity) {
            $this->quantityWarning = "Only {$this->availableQuantity} item(s) available.";
        } else {
            $this->quantityWarning = null;
        }
    }
}

    public function updatedSelectedCategory($value): void
    {
        $this->subcategories = Subcategory::where('category_id', $value)->pluck('name', 'id')->toArray();
        $this->selectedSubcategory = null;
        $this->groups = [];
        $this->selectedGroup = null;
        $this->items = [];
        $this->selectedItem = null;
    }

    public function updatedSelectedSubcategory($value): void
{
    $this->groups = Group::where('subcategory_id', $value)->pluck('name', 'id')->toArray();
    $this->selectedGroup = null;
    $this->selectedItem = null;

    // Load items without group within the selected subcategory
    $this->items = Item::whereNull('group_id')
        ->where('subcategory_id', $value)
        ->pluck('name', 'id')
        ->map(fn ($name) => $name . ' (Ungrouped)')
        ->toArray();
}

    public function updatedSelectedGroup($value): void
    {
        $this->items = Item::where('group_id', $value)->pluck('name', 'id')->toArray();
        $this->selectedItem = null;
    }

    public function addItem(): void
    {
        if (!$this->selectedItem || $this->quantity < 1) return;

        $item = Item::find($this->selectedItem);

        if (!$item) {
            $this->addError('selectedItem', 'Invalid item selected.');
            return;
        }

        if ($this->quantity > $item->quantity) {
            $this->addError('quantity', "Only {$item->quantity} item(s) available.");
            return;
        }

        if (collect($this->addedItems)->pluck('item_id')->contains($item->id)) {
            $this->addError('selectedItem', 'This item is already added.');
            return;
        }

        $this->addedItems[] = [
            'category_id' => $this->selectedCategory,
            'subcategory_id' => $this->selectedSubcategory,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'quantity' => $this->quantity,
        ];

        $this->dispatch('updateItems', addedItems: $this->addedItems);


        $this->selectedItem = null;
        $this->quantity = 1;
    }

    public function removeItem(int $index): void
    {
        unset($this->addedItems[$index]);
        $this->addedItems = array_values($this->addedItems);

        $this->dispatch('updateItems', addedItems: $this->addedItems);

    }

    public function render()
    {
        return view('livewire.event-item-selector');
    }
}
