<?php

declare(strict_types=1);

namespace soradore\DDUI;

use soradore\DDUI\element\ButtonElement;
use soradore\DDUI\element\CloseButtonElement;
use soradore\DDUI\element\DropdownElement;
use soradore\DDUI\element\HeaderElement;
use soradore\DDUI\element\LabelElement;
use soradore\DDUI\element\SliderElement;
use soradore\DDUI\element\SpacerElement;
use soradore\DDUI\element\TextFieldElement;
use soradore\DDUI\element\ToggleElement;
use soradore\DDUI\element\options\ButtonOptions;
use soradore\DDUI\element\options\CloseButtonOptions;
use soradore\DDUI\element\options\DropdownOptions;
use soradore\DDUI\element\options\HeaderOptions;
use soradore\DDUI\element\options\LabelOptions;
use soradore\DDUI\element\options\SliderOptions;
use soradore\DDUI\element\options\SpacerOptions;
use soradore\DDUI\element\options\TextFieldOptions;
use soradore\DDUI\element\options\ToggleOptions;
use soradore\DDUI\properties\StringProperty;

class CustomForm extends DataDrivenScreen
{
    public function __construct(string $title = '')
    {
        parent::__construct();
        $this->setTitle($title);
    }

    public function getIdentifier(): string
    {
        return 'minecraft:custom_form';
    }

    public function getDataStoreProperty(): string
    {
        return 'custom_form_data';
    }

    public function setTitle(string $title): static
    {
        $this->setProperty(new StringProperty('title', $title, $this));

        return $this;
    }

    public function setTitleObservable(Observable $title): static
    {
        $property = new StringProperty('title', $title->getValue(), $this);
        $title->subscribe(function (string $v) use ($property): ?StringProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    public function button(string $label, ?callable $onClick = null, ?ButtonOptions $options = null): static
    {
        $element = new ButtonElement($label, $options, $this->layout);
        if ($onClick !== null) {
            $element->onClick($onClick);
        }
        $this->layout->setProperty($element);

        return $this;
    }

    public function closeButton(?callable $onClick = null, ?CloseButtonOptions $options = null): static
    {
        $element = new CloseButtonElement($options, $this);
        if ($onClick !== null) {
            $element->onClick($onClick);
        }
        $this->setProperty($element);

        return $this;
    }

    /**
     * @param string[] $items Display labels for each dropdown option.
     */
    public function dropdown(
        string $label,
        array $items,
        Observable $selected,
        ?callable $onChange = null,
        ?DropdownOptions $options = null,
    ): static {
        $element = new DropdownElement($label, $items, $selected, $options, $this->layout);
        if ($onChange !== null) {
            $element->onChange($onChange);
        }
        $this->layout->setProperty($element);

        return $this;
    }

    public function header(string $text, ?HeaderOptions $options = null): static
    {
        $element = new HeaderElement($text, $options, $this->layout);
        $this->layout->setProperty($element);

        return $this;
    }

    public function label(string $text, ?LabelOptions $options = null): static
    {
        $element = new LabelElement($text, $options, $this->layout);
        $this->layout->setProperty($element);

        return $this;
    }

    public function slider(
        string $label,
        Observable $value,
        float $min,
        float $max,
        ?callable $onChange = null,
        ?SliderOptions $options = null,
    ): static {
        $element = new SliderElement($label, $value, $min, $max, $options, $this->layout);
        if ($onChange !== null) {
            $element->onChange($onChange);
        }
        $this->layout->setProperty($element);

        return $this;
    }

    public function spacer(?SpacerOptions $options = null): static
    {
        $element = new SpacerElement($options, $this->layout);
        $this->layout->setProperty($element);

        return $this;
    }

    public function textField(
        string $label,
        Observable $text,
        ?callable $onChange = null,
        ?TextFieldOptions $options = null,
    ): static {
        $element = new TextFieldElement($label, $text, $options, $this->layout);
        if ($onChange !== null) {
            $element->onChange($onChange);
        }
        $this->layout->setProperty($element);

        return $this;
    }

    public function toggle(
        string $label,
        Observable $value,
        ?callable $onChange = null,
        ?ToggleOptions $options = null,
    ): static {
        $element = new ToggleElement($label, $value, $options, $this->layout);
        if ($onChange !== null) {
            $element->onChange($onChange);
        }
        $this->layout->setProperty($element);

        return $this;
    }
}
