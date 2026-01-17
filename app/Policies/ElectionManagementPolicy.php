<?php

namespace App\Policies;

use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;

class ElectionPolicy
{
    /**
     * Membuat election baru
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Mengubah data election
     * - DRAF  : boleh
     * - DIBUKA: boleh (tapi nanti service akan reset votes & balik ke draf)
     * - DITUTUP: tidak boleh
     */
    public function update(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && ! $election->trashed()
            && in_array($election->status, [
                ElectionStatus::DRAF,
                ElectionStatus::DIBUKA,
            ], true);
    }

    /**
     * Membuka election
     * - hanya dari DRAF
     */
    public function open(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && ! $election->trashed()
            && $election->status === ElectionStatus::DRAF;
    }

    /**
     * Menutup election
     * - hanya dari DIBUKA
     */
    public function close(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && ! $election->trashed()
            && $election->status === ElectionStatus::DIBUKA;
    }

    /**
     * Soft delete election
     * - tidak boleh jika sedang dibuka
     */
    public function delete(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && ! $election->trashed()
            && $election->status !== ElectionStatus::DIBUKA;
    }

    /**
     * Restore election dari trash
     */
    public function restore(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && $election->trashed();
    }

    /**
     * Hapus permanen election
     */
    public function forceDelete(User $user, Election $election): bool
    {
        return $user->isAdmin()
            && $election->trashed();
    }
}
