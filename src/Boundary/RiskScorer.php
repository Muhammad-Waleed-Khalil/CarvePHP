<?php

declare(strict_types=1);

namespace Carve\Boundary;

use Carve\Boundary\ValueObjects\RiskScore;

final class RiskScorer
{
    public function score(
        CouplingScorer $couplingScorer,
        mixed $couplingResult,
        array $cluster,
    ): RiskScore {
        return new RiskScore(
            score: 0.0,
            components: [
                'coupling_score' => 0.0,
                'shared_table_write_score' => 0.0,
                'transaction_complexity_score' => 0.0,
                'raw_sql_uncertainty_score' => 0.0,
                'missing_test_signal_score' => 0.5,
            ],
        );
    }

    public function scoreFromData(float $couplingScore, array $cluster): RiskScore
    {
        $sharedTableWriteScore = 0.0;
        $transactionComplexityScore = 0.0;
        $rawSqlUncertaintyScore = 0.0;
        $missingTestSignalScore = 0.5;

        $risk = 0.30 * $couplingScore
            + 0.25 * $sharedTableWriteScore
            + 0.20 * $transactionComplexityScore
            + 0.15 * $rawSqlUncertaintyScore
            + 0.10 * $missingTestSignalScore;

        return new RiskScore(
            score: min($risk, 1.0),
            components: [
                'coupling_score' => $couplingScore,
                'shared_table_write_score' => $sharedTableWriteScore,
                'transaction_complexity_score' => $transactionComplexityScore,
                'raw_sql_uncertainty_score' => $rawSqlUncertaintyScore,
                'missing_test_signal_score' => $missingTestSignalScore,
            ],
        );
    }
}
