<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('authors.manage');
    }

    public function rules(): array
    {
        $author = $this->route('author');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('authors', 'slug')->ignore($author instanceof Author ? $author->id : null)],
            'bio' => ['nullable', 'string', 'max:5000'],
            'email' => ['nullable', 'email', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
