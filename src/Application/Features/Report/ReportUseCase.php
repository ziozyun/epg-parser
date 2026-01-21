<?php

declare(strict_types = 1);

namespace App\Application\Features\Report;

use App\Application\Interfaces\Repositories\ReportRepositoryInterface;

class ReportUseCase
{
  public function __construct(private ReportRepositoryInterface $reportRepository)
  {
  }

  public function generateReport(ReportRequest $request): ReportResponse
  {
    $rows = $this->reportRepository->programsCountByChannel($request->lang());
    $total = $this->reportRepository->totalProgramsCount();

    return new ReportResponse($total, $rows);
  }
}
