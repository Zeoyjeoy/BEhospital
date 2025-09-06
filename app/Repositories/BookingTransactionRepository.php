<?php
namespace App\Repositories;
use App\Models\BookingTransaction;

class BookingTransactionRepository{

    //manager queries
    public function getAll(){
        return BookingTransaction::with(['doctor', 'doctor.hospital', 'doctor.specialist', 'user'])
        ->latest()
        ->paginate(50);
    }
    public function getByIdForManager(int $id){
        return BookingTransaction::with(['doctor','doctor.hospital','doctor.specialistt','user'])
        ->latest()
        ->findOrFail($id);
    }
    public function updateStatus(int $id, int $status){
        $transaction =$this->getByIdForManager($id);
        $transaction->update(['status'=> $status]);
        return $transaction;
    }
    // customer queries
    public function getAllForUser( int $userId ){
        return BookingTransaction::where('user_id', $userId)
        ->with(['doctor', 'doctor.hospital', 'doctor.specialist'])
        ->latest()
        ->paginate(50);
    }
    public function getById(int $id , int $userId ){
        return BookingTransaction::where('id', $id)
        ->with(['doctor','doctor.hospital','doctor.specialist'])
        ->latest()
        ->paginate(10);
    }
    public function create(array $data)
    {   
        return BookingTransaction::create($data);
    }
    public function isTimeSlotTokenForDoctor(int $doctorId, string $date, string $time ){
        return BookingTransaction::where('doctor_id', $doctorId)
        ->where('start_date', $date)
        ->where('time_at', $time)
        ->exists();
    }
}