<?php

namespace App\Repositories\Contracts;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Patient Repository Interface
 *
 * Defines contract for patient data access operations
 */
interface PatientRepositoryInterface
{
    /**
     * Get all patients
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get paginated patients with active prenatal records
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Find patient by ID
     *
     * @param int $id
     * @return Patient|null
     */
    public function find(int $id): ?Patient;

    /**
     * Find patient by formatted ID (e.g., PT-001)
     *
     * @param string $formattedId
     * @return Patient|null
     */
    public function findByFormattedId(string $formattedId): ?Patient;

    /**
     * Create a new patient
     *
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient;

    /**
     * Update patient
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete patient (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Search patients by name or ID
     *
     * @param string $term
     * @return Collection
     */
    public function search(string $term): Collection;

    /**
     * Search patients with pagination
     *
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchPaginated(string $term, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get patients with active prenatal records
     *
     * @return Collection
     */
    public function withActivePrenatalRecords(): Collection;

    /**
     * Get patients with high risk status
     *
     * @return Collection
     */
    public function getHighRiskPatients(): Collection;

    /**
     * Get patient with full profile data (relationships loaded)
     *
     * @param int $id
     * @return Patient|null
     */
    public function getFullProfile(int $id): ?Patient;

    /**
     * Count total patients
     *
     * @return int
     */
    public function count(): int;

    /**
     * Count patients with active pregnancies
     *
     * @return int
     */
    public function countActivePregnancies(): int;

    /**
     * Find duplicate patient by name and age
     *
     * @param string $firstName
     * @param string $lastName
     * @param int $age
     * @return Patient|null
     */
    public function findDuplicate(string $firstName, string $lastName, int $age): ?Patient;

    /**
     * Find duplicate patient excluding specific ID
     *
     * @param string $firstName
     * @param string $lastName
     * @param int $age
     * @param int $excludeId
     * @return Patient|null
     */
    public function findDuplicateExcept(string $firstName, string $lastName, int $age, int $excludeId): ?Patient;

    /**
     * Find patient with specified relationships
     *
     * @param int $id
     * @param array $relations
     * @return Patient|null
     */
    public function findWithRelations(int $id, array $relations): ?Patient;

    /**
     * Get patient full profile for printing (ordered for documents)
     *
     * @param int $id
     * @return Patient|null
     */
    public function getFullProfileForPrint(int $id): ?Patient;

    /**
     * Search patients with filters (for AJAX requests)
     *
     * @param string|null $term
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function searchWithFilters(?string $term, array $filters = [], int $limit = 50): Collection;

    /**
     * Check if patient has prenatal records
     *
     * @param int $id
     * @return bool
     */
    public function hasPrenatalRecords(int $id): bool;
}
