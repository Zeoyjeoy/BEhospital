<?php

namespace App\Repositories;
use App\Models\Hospital;

class HospitalRepository{
    public function getAll(array $fields){
        return Hospital::select($fields)->latest()->paginate(10);
    }
    public function getById(int $id, array $fields)
    {
        return Hospital::select($fields)->with(['doctors.specialist','specialists'])->findOrFail($id);
    }
    public function create(array $data){
        return Hospital::create($data);

    }
    public function update(int $id, array $data){
        $hospital = Hospital::find($id);
        $hospital->update($data);
        return $hospital;
    }
    public function delete(int $id){
        $hospital = Hospital::find($id);
        $hospital->delete();
    }
}