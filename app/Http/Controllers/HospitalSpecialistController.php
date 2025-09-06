<?php

namespace App\Http\Controllers;
use App\Services\HospitalService;
use Illuminate\Http\Request;

class HospitalSpecialistController extends Controller
{
    //
    private $hospitalService;
    public function __construct(HospitalService $hospitalService){
        $this->hospitalService= $hospitalService;
    }
    public function attach(Request $request, int $hospital_id){
        $request->validate([
            'specialist_id' => 'required|exists:specialists,id',
        ]);
        $this->hospitalService->attachSpecialist(
            $hospital_id,
            $request->input('specialist_id'),
        );
        return response()->json([
            'message' => 'Specialist attached to hospital successfully']);
    }
    public function detach(Request $request, int $hospital_id){
        $this->hospitalService->detachSpecialist(
            $hospital_id,
            $request->input('specialist_id'),
        );
        return response()->json([
            'message' => 'Specialist detached successfully']);
        }

}
