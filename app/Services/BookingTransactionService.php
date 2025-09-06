<?php 
namespace App\Services;
use App\Repositories\BookingTransactionRepository;
use App\Repositories\DoctorRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
class BookingTransactionService{

    private $bookingTransactionRepository;
    private $doctorRepository;

    public function __construct(BookingTransactionRepository $bookingTransactionRepository,
    DoctorRepository $doctorRepository)
    {
        $this->bookingTransactionRepository = $bookingTransactionRepository;
        $this->doctorRepository = $doctorRepository;
    }

    //manager services
    public function getAll(){
        return $this->bookingTransactionRepository->getAll();
    }

    public function getByIdForManager(int $id){
        return $this->bookingTransactionRepository->getByIdForManager($id);
    }
    public function updateStatus(int $id, string $status){
        if (!in_array($status, ['Approved', 'Rejected'])){
            throw ValidationException::withMessages([
                'status'=> ['Invalid status value.']]);
            }
            return $this->bookingTransactionRepository->updateStatus($id, $status);
        }
    //customer services
    public function getAllForUser(int $userId){ 
        return $this->bookingTransactionRepository->getAllForUser($userId);
    }
    public function getById( int $id, int $userId){
        return $this->bookingTransactionRepository->getById($id, $userId);
    }
     public function create(array $data)
    {
        //  Auth facade untuk mendapatkan user ID
        if (!Auth::check()) {
            throw ValidationException::withMessages([
                'auth' => ['User harus login terlebih dahulu']
            ]);
        }
        
        $data['user_id'] = Auth::user()->id;

        if($this->bookingTransactionRepository->isTimeSlotTokenForDoctor(
            $data['doctor_id'], 
            $data['started_at'], 
            $data['time_at']
        )){
            throw ValidationException::withMessages([
                'time-at'=> ['Waktu yang dipilih untuk dokter ini sudah terisi.']
            ]);
        }

        $doctor = $this->doctorRepository->getById($data['doctor_id'],['*']);

        $price = $doctor->specialist->price;
        $tax = (int) round($price* .11);

        $grand = $price * $tax;

        $data['sub_total'] = $price;
        $data['tax_total'] = $tax;
        $data['grand_total'] = $grand;
        $data['status'] = 'Waiting';

        if(isset( $data['proof'] ) && $data['proof'] instanceof UploadedFile){
            $data['proof'] = $this->uploadedProof($data['proof']);
        }
        return $this->bookingTransactionRepository->create($data);
    }
    private function uploadedProof(UploadedFile $file){
        return $file->store('proofs', 'public');
    }
}