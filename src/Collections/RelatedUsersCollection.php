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
        $this->handleChilds($userId, $relatedUserId);

        //updating child's
        foreach ($this->relatedUsers as $key => $childs) {
            foreach ($childs as $child) {
                $this->handleChilds($key, $child);
            }
        }

        return $this;
    }

    private function handleChilds(string $userId, string $relatedUserId): void
    {
        foreach ($this->relatedUsers[$userId] as $childId) {
            $this->appendTo($childId, $userId);
            $this->appendTo($childId, $relatedUserId);
        }
    }

    private function appendTo(string $userId, $relatedUserId): void
    {
        if ($userId === $relatedUserId) {
            return ;
        }

        if (!isset($this->relatedUsers[$relatedUserId])) {
            $this->relatedUsers[$relatedUserId] = [$userId];
        } else {
            $this->relatedUsers[$relatedUserId][] = $userId;
            $this->relatedUsers[$relatedUserId] = array_unique($this->relatedUsers[$relatedUserId]);
        }

        if (!isset($this->relatedUsers[$userId])) {
            $this->relatedUsers[$userId] = [$relatedUserId];
        } else {
            $this->relatedUsers[$userId][] = $relatedUserId;
            $this->relatedUsers[$userId] = array_unique($this->relatedUsers[$userId]);
        }
    }

    public function getByUserId(string $userId): ?array
    {
        return $this->relatedUsers[$userId] ?? null;
    }

    public function getAll(): array
    {
        return $this->relatedUsers;
    }
}
