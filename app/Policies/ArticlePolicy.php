<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('articles.view');
    }

    public function view(User $user, Article $article): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasPermission('articles.review') || $user->hasPermission('articles.publish')) {
            return true;
        }

        return $user->hasPermission('articles.update')
            && $article->author?->user_id === $user->id
            && in_array($article->status, [ArticleStatus::Draft, ArticleStatus::Rejected], true);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.delete');
    }

    public function submit(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.submit') && $this->update($user, $article);
    }

    public function review(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.review');
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->hasPermission('articles.publish');
    }
}
