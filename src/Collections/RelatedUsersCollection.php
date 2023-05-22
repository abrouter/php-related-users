<?php

declare(strict_types=1);

namespace Abrouter\RelatedUsers\Collections;

class RelatedUsersCollection
{
    private array $relatedUsers;

    public function __construct(array $initial = [])
    {
        $this->relatedUsers = $initial;
    }

    public function append(string $userId, string $relatedUserId): self
    {
        $this->appendTo($userId, $relatedUserId);

        return $this;
    }

    private function appendTo(string $userId, string $relatedUserId): void
    {
        if ($userId === $relatedUserId) {
            return ;
        }

        $this->appendAppend($userId, $relatedUserId);
        $this->appendAppend($relatedUserId, $userId);
    }

    private function appendAppend(string $userId, string $relatedUserId): self
    {
        $this->init($relatedUserId);
        $this->merge($relatedUserId, $this->getMergedChilds($userId, [$relatedUserId]));
        $this->addRelatedUser($relatedUserId, $userId);
        $this->unique($relatedUserId);
        $this->regenerateChilds($userId);

        return $this;
    }

    private function regenerateChilds(string $userId): self
    {
        $childs = $this->relatedUsers[$userId] ?? []; // [a1, a2,a]
        foreach ($childs as $child) {
            $childChilds = $this->relatedUsers[$child]; // [a]
            $newChildChilds = $childChilds;
            foreach ($childChilds as $childChild) {
                $newChildChilds = array_unique(array_merge(
                    $newChildChilds,
                    $this->relatedUsers[$childChild]
                ));
            }

            $newChildChilds = array_filter($newChildChilds, function ($value) use ($child) {
                return $value !== $child;
            });


            $this->relatedUsers[$child] = array_unique($newChildChilds);
        }


        return $this;
    }

    private function addRelatedUser(string $dist, string $userId): self
    {
        $this->relatedUsers[$dist][] = $userId;
        return $this;
    }

    private function merge(string $userId, array $value): self
    {
        $this->relatedUsers[$userId] = array_merge($this->relatedUsers[$userId], $value);
        return $this;
    }

    private function init(string $userId): self
    {
        if (!isset($this->relatedUsers[$userId])) {
            $this->relatedUsers[$userId] = [];
        }

        return $this;
    }

    private function unique(string $userId): self
    {
        $this->relatedUsers[$userId] = array_unique($this->relatedUsers[$userId]);
        return $this;
    }

    public function getMergedChilds(string $userId, array $except): array
    {
        $mergedChilds = [];
        $childs = $this->relatedUsers[$userId] ?? null;
        if ($childs === null) {
            return [];
        }

        foreach ($childs as $child) {
            $mergedChilds[] = $child;
            $mergedChilds = array_merge($mergedChilds, $this->relatedUsers[$child] ?? []);
        }

        $except[] = $userId;
        $mergedChilds = array_filter($mergedChilds, function ($value) use ($except) {
            return !in_array($value, $except);
        });

        return $mergedChilds;
    }

    public function getByUserId(string $userId): ?array
    {
        return empty($this->relatedUsers[$userId]) ? null : array_values($this->relatedUsers[$userId]);
    }

    public function getAll(): array
    {
        return $this->relatedUsers;
    }

    public function getAllApply($func): array
    {
        return array_map(function (array $val) use ($func) {
            $func($val);
            return $val;
        }, $this->relatedUsers);
    }
}
