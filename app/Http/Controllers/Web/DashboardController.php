<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\TestRepository;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $testRepository;

    public function __construct(TestRepository $testRepository)
    {
        $this->testRepository = $testRepository;
    }

    public function index()
    {
       $tests = $this->testRepository->getUserTestHistory(Auth::id(), paginate: true);


        return view('pages.dashboard.index', compact('tests'));
    }
}
