<?php

namespace App\Services;

use App\Models\Hospital;
use App\Repositories\DoctorRepository;
use App\Repositories\HospitalSpecialistRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DoctorService
{
    private $doctorRepository;
    private $hospitalSpecialistRepository;

    public function __construct(
        DoctorRepository $doctorRepository,
        HospitalSpecialistRepository $hospitalSpecialistRepository
    )
    {
        $this->doctorRepository = $doctorRepository;
        $this->hospitalSpecialistRepository = $hospitalSpecialistRepository;
    }
    public function getAll(array $fields){
        return $this->doctorRepository->getAll($fields);
    }
    public function getById(int $id, array $fields){
        return $this->doctorRepository->getById($id, $fields);
    }
    public function create(array $data)
{
    if (!$this->hospitalSpecialistRepository->existsForHospitalAndSpecialist(
        $data['hospital_id'],
        $data['specialist_id'],
    )) {
        throw ValidationException::withMessages([
            'specialist_id' => ['Selected specialist is not available in the selected hospital.']
        ]);
    }

    if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
        $data['photo'] = $this->uploadPhoto($data['photo']);
    }

    return $this->doctorRepository->create($data);
}

    private function uploadPhoto(UploadedFile $photo){
        return $photo->store('doctors', 'public');
    }
    private function deletePhoto(string $photoPath){
        $relativePath = 'doctors/'.basename($photoPath);
        if(Storage::disk('public')->exists($relativePath)){
            Storage::disk('')->delete($relativePath);
        }
    }
    public function update(int $id, array $data)
{
    $doctor = $this->doctorRepository->getById($id, ['*']);

    if (!$this->hospitalSpecialistRepository->existsForHospitalAndSpecialist(
        $data['hospital_id'],
        $data['specialist_id'],
    )) {
        throw ValidationException::withMessages([
            'specialist_id' => ['Selected specialist is not available in the selected hospital.']
        ]);
    }

    if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
        // Delete old photo if exists
        if ($doctor->photo) {
            $this->deletePhoto($doctor->photo);
        }
        $data['photo'] = $this->uploadPhoto($data['photo']);
    }

    return $this->doctorRepository->update($id, $data);
}

    public function filterBySpecialistAndHospital($hospitalId, $specialistId){
        return $this->doctorRepository->filterBySpecialistAndHospital($hospitalId, $specialistId);
    }
    public function delete(int $id)
{
    $doctor = $this->doctorRepository->getById($id, ['*']);
    
    // Delete photo if exists
    if ($doctor->photo) {
        $this->deletePhoto($doctor->photo);
    }
    
    return $this->doctorRepository->delete($id);
}
    public function getAvailableSlots(int $doctorId){
        $doctor = $this->doctorRepository->getById($doctorId, ['id']);

        $dates = collect([
            now()->addDays(1)->startOfDay(),
            now()->addDays(2)->startOfDay(),
            now()->addDays(3)->startOfDay(),
        ]);

        $timeSlots = ['10:30', '11:30', '13:30', '14:30','15:30', '16:30'];

        $availability = [];

        foreach($dates as $date){
            $dateStr = $date->toDateString();
            $availability[$dateStr] = [];

            foreach($timeSlots as $time){
                $isTaken = $doctor->bookingTransactions()
                ->whereDate('started_at', $dateStr)
                ->whereTime('time_at', $time)
                ->exists();

                if($isTaken){
                    $availability[$dateStr][] = $time;
                }
            }
        }
        return $availability;
    }
}