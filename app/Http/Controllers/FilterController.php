<?phpnamespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyediaJasa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_budget' => 'required',
            'end_budget' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'date_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $startBudget = $request->input('start_budget');
        $endBudget = $request->input('end_budget');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $dateTime = $request->input('date_time');
        $provinsiPenyedia = $request->input('provinsi_penyedia');

        $date = Carbon::parse($dateTime);
        $dayOfWeek = $date->dayOfWeekIso;

        $penyediaJasa = PenyediaJasa::query()
            ->whereHas('Jadwal', function ($query) use ($dayOfWeek, $startTime, $endTime) {
                $query->where('hari', $dayOfWeek)
                      ->where('jam_buka', '<=', $startTime)
                      ->where('jam_tutup', '>=', $endTime);
            })
            ->whereDoesntHave('TanggalLibur', function ($query) use ($date) {
                $query->where('tanggal_awal', '<=', $date)
                      ->where('tanggal_akhir', '>=', $date);
            })
            ->where(function ($query) use ($date, $startTime, $endTime) {
                $query->where(function ($query) {
                    $query->whereHas('Paket.DetailTransaksi.Transaksi', function ($query) {
                        $query->whereNotNull('status_transaksi');
                    })
                    ->where(function ($query) use ($date, $startTime, $endTime) {
                        $query->where(function ($query) use ($date) {
                            $query->where('tanggal_pelaksanaan', '!=', $date);
                        })
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('tanggal_pelaksanaan', '=', $date)
                                  ->where(function ($query) use ($startTime, $endTime) {
                                      $query->where('jam_mulai', '>=', $endTime)
                                            ->orWhere('jam_selesai', '<=', $startTime);
                                  });
                        });
                    });
                })
                ->orWhereDoesntHave('Paket.DetailTransaksi.Transaksi', function ($query) {
                    $query->whereNotNull('status_transaksi');
                });
            })
            ->whereHas('Paket', function ($query) use ($startBudget, $endBudget) {
                $query->where('harga_paket', '>=', $startBudget)
                      ->where('harga_paket', '<=', $endBudget);
            });

        if ($provinsiPenyedia !== 'Semua') {
            $penyediaJasa->where('provinsi_penyedia', $provinsiPenyedia);
        }

        $result = $penyediaJasa->get();

        return response()->json($result);
    }
}
