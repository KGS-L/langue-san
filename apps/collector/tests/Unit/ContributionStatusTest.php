<?php

namespace Tests\Unit;

use App\Enums\ContributionStatus;
use PHPUnit\Framework\TestCase;

class ContributionStatusTest extends TestCase
{
    public function test_internal_and_public_labels_are_intentionally_different(): void
    {
        $this->assertSame('À transcrire', ContributionStatus::PENDING->label());
        $this->assertSame('Reçue', ContributionStatus::PENDING->publicLabel());
        $this->assertSame('En vérification', ContributionStatus::VALIDATED_ONCE->publicLabel());
        $this->assertSame('Validée', ContributionStatus::APPROVED->publicLabel());
        $this->assertSame('Non retenue', ContributionStatus::REJECTED->publicLabel());
    }

    public function test_terminal_statuses_are_only_approved_and_rejected(): void
    {
        $this->assertTrue(ContributionStatus::APPROVED->isTerminal());
        $this->assertTrue(ContributionStatus::REJECTED->isTerminal());
        $this->assertFalse(ContributionStatus::VALIDATED_TWICE->isTerminal());
    }
}
