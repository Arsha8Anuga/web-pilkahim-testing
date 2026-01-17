<?php

namespace App\Service\Election;

use App\DTO\Election\CreateElectionDTO;
use App\DTO\Election\UpdateElectionDTO;
use App\Enums\ElectionStatus;
use App\Models\Election;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use RuntimeException;

class ElectionService
{

    public function create(CreateElectionDTO $dto): Election {
        return DB::transaction(function () use ($dto) {
            return Election::create($dto->toArray());
        });
    }


    public function update(Election $election, UpdateElectionDTO $dto): Election {
        return DB::transaction(function () use ($election, $dto) {

            $locked = $this->lockElection($election->id);

            if ($locked->status->value ===  ElectionStatus::DIBUKA ) {
                // hapus semua vote terkait
                $locked->votes()->delete();

                // balikin ke draf
                $locked->status = ElectionStatus::DRAF;
            }

            $locked->update($dto->toArray());

            return $locked;
        });
    }

    public function open(Election $election): Election {
        return DB::transaction(function () use ($election) {

            $locked = $this->lockElection($election->id);

            if ($locked->candidates()->count() < 2) {
                throw new RuntimeException('Jumlah kandidat belum mencukupi');
            }

            Election::where('status',  ElectionStatus::DIBUKA)
                ->where('id', '!=', $locked->id)
                ->lockForUpdate()
                ->update(['status' =>  ElectionStatus::DITUTUP ]);

            $locked->update([
                'status' =>  ElectionStatus::DIBUKA,
            ]);

            return $locked;
        });
    }
    public function close(Election $election): Election {
        return DB::transaction(function () use ($election) {

            $locked = $this->lockElection($election->id);

            $locked->update([
                'status' =>  ElectionStatus::DITUTUP,
            ]);

            return $locked;
        });
    }

    public function delete(Election $election): Election
    {
        return DB::transaction(function () use ($election) {

            $locked = $this->lockElection($election->id);

            $locked->delete();

            return $locked;
        });
    }

    public function restore(int $id): Election
    {
        return DB::transaction(function () use ($id) {

            $election = Election::onlyTrashed()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $election->restore();

            return $election;
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {

            $election = Election::onlyTrashed()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $election->votes()->delete();
            $election->candidates()->delete();

            $election->forceDelete();
        });
    }

    private function lockElection(int $id): Election
    {
        return Election::whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
