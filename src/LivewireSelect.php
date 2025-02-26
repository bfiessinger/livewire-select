<?php

namespace Asantibanez\LivewireSelect;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Class LivewireSelect
 * @package Asantibanez\LivewireSelect
 * @property string $name
 * @property string $placeholder
 * @property mixed $value
 * @property boolean $searchable
 * @property string $searchTerm
 * @property array $dependsOn
 * @property array $dependsOnValues
 * @property boolean $waitForDependenciesToShow
 * @property string $noResultsMessage
 * @property string $selectView
 * @property string $defaultView
 * @property string $searchView
 * @property string $searchInputView
 * @property string $searchOptionsContainer
 * @property string $searchOptionItem
 * @property string $searchSelectedOptionView
 * @property string $searchNoResultsView
 */
class LivewireSelect extends Component
{
    public $name;
    public $placeholder;

    public $value;
    public $initValueEncoded;
    public $optionsValues;

    public $searchable;
    public $searchTerm;

    public $multiple;

    public $dependsOn;
    public $dependsOnValues;

    public $waitForDependenciesToShow;

    public $noResultsMessage;

    public $selectView;
    public $defaultView;
    public $multipleView;
    public $searchView;
    public $searchInputView;
    public $searchOptionsContainer;
    public $searchOptionItem;
    public $searchSelectedOptionView;
    public $searchNoResultsView;

    public function mount($name,
                          $value = null,
                          $placeholder = 'Select an option',
                          $searchable = false,
                          $multiple = false,
                          $dependsOn = [],
                          $dependsOnValues = [],
                          $waitForDependenciesToShow = false,
                          $noResultsMessage = 'No options found',
                          $selectView = 'livewire-select::select',
                          $defaultView = 'livewire-select::default',
                          $multipleView = 'livewire-select::multiple',
                          $searchView = 'livewire-select::search',
                          $searchInputView = 'livewire-select::search-input',
                          $searchOptionsContainer = 'livewire-select::search-options-container',
                          $searchOptionItem = 'livewire-select::search-option-item',
                          $searchSelectedOptionView = 'livewire-select::search-selected-option',
                          $searchNoResultsView = 'livewire-select::search-no-results',
                          $extras = [])
    {
        $this->name = $name;
        $this->placeholder = $placeholder;

        $this->value = $value;
        $this->initValueEncoded = json_encode($value);

        $this->searchable = $searchable;
        $this->searchTerm = '';

        $this->multiple = !!$multiple;

        $this->dependsOn = $dependsOn;

        $this->dependsOnValues = collect($this->dependsOn)
            ->mapWithKeys(function ($key) use ($dependsOnValues) {
                $value = collect($dependsOnValues)->get($key);

                return [
                    $key => $value,
                ];
            })
            ->toArray();

        $this->waitForDependenciesToShow = $waitForDependenciesToShow;

        $this->noResultsMessage = $noResultsMessage;

        $this->selectView = $selectView;
        $this->defaultView = $defaultView;
        $this->multipleView = $multipleView;
        $this->searchView = $searchView;
        $this->searchInputView = $searchInputView;
        $this->searchOptionsContainer = $searchOptionsContainer;
        $this->searchOptionItem = $searchOptionItem;
        $this->searchSelectedOptionView = $searchSelectedOptionView;
        $this->searchNoResultsView = $searchNoResultsView;

        $this->afterMount($extras);
    }

    public function afterMount($extras = [])
    {
        //
    }

    public function options($searchTerm = null) : Collection
    {
        return collect();
    }

    public function selectedOption($value)
    {
        return $value;
    }

    public function notifyValueChanged()
    {
        $this->emit("{$this->name}Updated", [
            'name' => $this->name,
            'value' => $this->value,
        ]);
    }

    public function selectValue($value)
    {
        $this->value = $value;

        if ($this->searchable && $this->value == null) {
            $this->emit('livewire-select-focus-search', ['name' => $this->name]);
        }

        if ($this->searchable && $this->value != null) {
            $this->emit('livewire-select-focus-selected', ['name' => $this->name]);
        }

        $this->notifyValueChanged();
    }

    public function updatedValue()
    {
        $this->selectValue($this->value);
    }

    public function getListeners()
    {
        return collect($this->dependsOn)
            ->mapWithKeys(function ($key) {
                return ["{$key}Updated" => 'updateDependingValue'];
            })->merge($this->listeners)
            ->toArray();
    }

    public function updateDependingValue($data)
    {
        $name = $data['name'];
        $value = $data['value'];

        $oldValue = $this->getDependingValue($name);

        $this->dependsOnValues = collect($this->dependsOnValues)
            ->put($name, $value)
            ->toArray();

        if ($oldValue != null && $oldValue != $value) {
            $this->value = null;
            $this->searchTerm = null;
            $this->notifyValueChanged();
        }
    }

    public function hasDependency($name)
    {
        return collect($this->dependsOnValues)->has($name);
    }

    public function getDependingValue($name)
    {
        return collect($this->dependsOnValues)->get($name);
    }

    public function isSearching()
    {
        return !empty($this->searchTerm);
    }

    public function allDependenciesMet()
    {
        return collect($this->dependsOnValues)
            ->reject(function ($value) {
                return $value != null;
            })
            ->isEmpty();
    }

    public function styles()
    {
        return config('livewire-select')['styles'];
    }

    public function css($options = null) {
        $assets = [
            'livewire' => Livewire::styles(),
            'tailwindcss' => '<link rel="stylesheet" href="' . asset('/vendor/tailwind.css') . '" />',
        ];

        return $this->renderAssets('css', $assets, $options);
    }

    public function js($options = null) {
        $assets = [
            'livewire' => Livewire::scripts(),
            'alpine' => '<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>',
            'livewire-select' => '<script>
                window.livewire.on(\'livewire-select-focus-search\', (data) => {
                    const el = document.getElementById(`${data.name || \'invalid\'}`);

                    if (!el) {
                        return;
                    }

                    el.focus();
                });

                window.livewire.on(\'livewire-select-focus-selected\', (data) => {
                    const el = document.getElementById(`${data.name || \'invalid\'}-selected`);

                    if (!el) {
                        return;
                    }

                    el.focus();
                });
            </script>',
            'livewire-select-multiple' => '<script>
            function livewireSelectMultiSelectDropdown($el) {
                // Select the node that will be observed for mutations
                var relatedSelectTargetNode = $el.querySelector(\'.livewire-select-input\');

                // Options for the observer (which mutations to observe)
                var relatedSelectMutationObserverConfig = { attributes: true, childList: true, subtree: true };

                // Callback function to execute when mutations are observed
                const relatedSelectMutationObserverCallback = function(mutationsList, observer) {

                    var hasUpdates = false;

                    // Use traditional for loops for IE 11
                    for(const mutation of mutationsList) {
                        if (mutation.type === \'childList\') {
                            hasUpdates = true;
                            break;
                        }
                    }

                    if (hasUpdates) {
                        relatedSelectTargetNode.dispatchEvent(new CustomEvent(\'livewireselectoptionsloaded\', { bubbles: true }));
                    }
                };

                // Create an observer instance linked to the callback function
                var relatedSelectMutationObserver = new MutationObserver(relatedSelectMutationObserverCallback);

                // Start observing the target node for configured mutations
                relatedSelectMutationObserver.observe(relatedSelectTargetNode, relatedSelectMutationObserverConfig);

                return {
                    options: [],
                    selected: [],
                    show: false,
                    open() { this.show = true },
                    close() { this.show = false },
                    isOpen() { return this.show === true },
                    select(index, event) {
                        if (!this.options[index].selected) {
                            this.options[index].selected = true;
                            if (this.selected.indexOf(index) === -1) {
                                this.selected.push(index);
                            }
                        } else {
                            this.selected.splice(this.selected.lastIndexOf(index), 1);
                            this.options[index].selected = false
                        }
                    },
                    remove(index, option) {
                        this.options[option].selected = false;
                        this.selected.splice(index, 1);
                    },
                    loadOptions(selectEl) {
                        this.options = [];
                        const options = selectEl.options;
                        for (let i = 0; i < options.length; i++) {

                            let isSelected = false;
                            if (options[i].getAttribute(\'selected\') != null) {
                                if (this.selected.indexOf(i) === -1) {
                                    this.selected.push(i);
                                }
                                isSelected = true;
                            }

                            this.options.push({
                                value: options[i].value,
                                text: options[i].innerText,
                                selected: isSelected
                            });
                        }
                    },
                    selectedValues(){
                        if (!this.options.length) {
                            return [];
                        }

                        return this.selected.map((option)=>{
                            return this.options[option].value;
                        })
                    }
                }

            }
            </script>'
        ];

        return $this->renderAssets('js', $assets, $options);
    }

    public function render()
    {
        if ($this->searchable) {
            if ($this->isSearching()) {
                $options = $this->options($this->searchTerm);
            } else {
                $options = collect();
            }
        } else {
            $options = $this->options($this->searchTerm);
        }

        $this->optionsValues = $options->pluck('value')->toArray();

        if ($this->value != null) {
            $selectedOption = $this->selectedOption($this->value);
        }

        $shouldShow = $this->waitForDependenciesToShow
            ? $this->allDependenciesMet()
            : true;

        $styles = $this->styles();

        return view($this->selectView)
            ->with([
                'initValueEncoded' => $this->initValueEncoded,
                'options' => $options,
                'selectedOption' => $selectedOption ?? null,
                'shouldShow' => $shouldShow,
                'styles' => $styles,
            ]);
    }

    /**
     * Generate a string of required assets
     *
     * @param array $assets
     * @param array $options
     *
     * @return string
     */
    private function renderAssets($assetType = 'js', $assets = [], $options = null) {
        if ($options) {
            $options = explode(',', $options);
            $options = array_map('trim', $options);

            // include mandatory assets
            switch ($assetType) {
                case 'js':
                    if (!in_array('livewire-select', $options)) {
                        $options[] = 'livewire-select';
                    }
                    break;
                default:
                    break;
            }

            $assetArray = [];

            foreach ($assets as $asset => $link) {
                if (in_array($asset, $options)) {
                    $assetArray[] = $link;
                }
            }
        } else {
            $assetArray = $assets;
        }

        $assetStr = implode(PHP_EOL, $assetArray);
        return <<<HTML
            {$assetStr}
        HTML;
    }
}
