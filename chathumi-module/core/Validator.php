<?php

/**
 * Minimal server-side validation helper (no external libraries).
 * Usage:
 *   $v = new Validator($_POST);
 *   $v->required('email')->email('email')->required('password')->minLength('password', 8);
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function value(string $field) { return trim((string) ($this->data[$field] ?? '')); }

    public function required(string $field, string $label = ''): static
    {
        if ($this->value($field) === '') {
            $this->errors[$field][] = ($label ?: ucfirst(str_replace('_', ' ', $field))) . ' is required.';
        }
        return $this;
    }

    public function email(string $field): static
    {
        if ($this->value($field) !== '' && !filter_var($this->value($field), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Enter a valid email address.';
        }
        return $this;
    }

    public function minLength(string $field, int $len): static
    {
        if (strlen($this->value($field)) > 0 && strlen($this->value($field)) < $len) {
            $this->errors[$field][] = ucfirst($field) . " must be at least $len characters.";
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $message = 'Fields do not match.'): static
    {
        if ($this->value($field) !== $this->value($otherField)) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function fails(): bool { return count($this->errors) > 0; }
    public function errors(): array { return $this->errors; }
    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) return $fieldErrors[0];
        return null;
    }
}
