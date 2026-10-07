<?php

namespace App\Http\Requests\Admin;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $article = $this->route('article');

        if ($article instanceof Article) {
            return (bool) $this->user()?->can('update', $article);
        }

        return (bool) $this->user()?->can('create', Article::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('articles', 'slug')->ignore($article instanceof Article ? $article->id : null)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'author_id' => ['nullable', 'exists:authors,id'],
            'tags' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'remove_featured_image' => ['sometimes', 'boolean'],
            'is_breaking' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_trending' => ['sometimes', 'boolean'],
            'is_opinion' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'action' => ['nullable', 'in:save,submit,approve,reject,publish,schedule,archive'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required_if:action,schedule', 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'featured_image.uploaded' => 'The image did not reach the server. Use a JPEG, PNG, GIF, or WebP under 5 MB.',
            'featured_image.max' => 'The image must be 5 MB or smaller.',
            'featured_image.mimes' => 'The image must be a JPEG, PNG, GIF, or WebP file.',
        ];
    }
}
