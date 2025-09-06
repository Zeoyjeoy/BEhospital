<?php

namespace App\Http\Controllers;

use App\Http\Resources\DoctorResource;
use App\Http\Requests\DoctorRequest;
use App\Services\DoctorService;
use App\Http\Requests\SpecialistHospitalDoctorRequest;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    //
    private $doctorServices;

    public function __construct(DoctorService $doctorServices){
        $this->doctorServices = $doctorServices;
    }
    public function index()
    {
        $fields = ['id', 'name', 'photo', 'specialist_id', 'hospital_id'];
        $doctors = $this->doctorServices->getAll($fields);
        return response()->json(DoctorResource::collection($doctors));
    }
    public function show (int $id){
        try{
            $fields = ['*'];
            $doctor = $this->doctorServices->getById($id, $fields);
            return response()->json(new DoctorResource($doctor));
        }catch(ModelNotFoundException $e){
            return response()->json([
                'message'=> 'Doctor not Found',
                ],404);
        }
    }
    public function store(DoctorRequest $request){
        $doctor = $this->doctorServices->create($request->validated());
        return response()->json(new DoctorResource($doctor),201);
    }
    public function update(DoctorRequest $request, int $id){
        try{
            $doctor = $this->doctorServices->update($id, $request->validated());
            return response()->json(new DoctorResource($doctor));
        }catch(ModelNotFoundException $e){
            return response()->json([
                'message'=> 'Doctor not found',
                ],404);
            }
    }
    public function destroy(int $id){
        try{
            $this->doctorServices->delete($id);
            return response()->json(
                ['message'=> 'Doctor deleted successfully'],
            );
        }catch(ModelNotFoundException $e){
            return response()->json([
                'message'=> 'Doctor not found',
                ],404);
            }
        }
    public function filterBySpecialistAndHospital(SpecialistHospitalDoctorRequest $request)
    {   
        $validate = $request->validated();

        $doctor = $this->doctorServices->filterBySpecialistAndHospital(
            $validate['hospital_id'],
            $validate['specialist_id'],
        );

        // $validate = $request->validate([
        //     'specialist_id' => 'required|exists:specialists,id',
        //     'hospital_id' => 'required|exists:hospitals,id',
        // ]);
        // $doctor = $this->doctorServices->filterBySpecialistAndHospital(
        //     $validate['hospital_id'],
        //     $validate['specialist_id'],
        // );
        return DoctorResource::collection($doctor);
    }
    public function availableSlots(int $doctorId){
        try{
            $availableSlots = $this->doctorServices->getAvailableSlots($doctorId);
            return response()->json(['data'=>$availableSlots]);
        }catch(ModelNotFoundException $e){
            return response()->json([
                'message'=> 'Doctor not found',
                ],404);
            }
    }

}
