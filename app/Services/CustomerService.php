<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;

class CustomerService
{
    public function findOrCreateCustomer(array $customerData): ?Customer
    {
        if (empty($customerData['email'])) {
            return null;
        }

        $customer = Customer::firstOrNew(['email' => $customerData['email']]);

        $this->updateCustomerData($customer, $customerData);
        $customer->last_active = now();
        $customer->save();

        return $customer;
    }

    public function handleCustomerUpdate(array $customerData, Booking $booking): ?Customer
    {
        if (empty($customerData['email'])) {
            return null;
        }

        $currentCustomer = $booking->customer;

        // If same customer, update existing
        if ($currentCustomer && $currentCustomer->email === $customerData['email']) {
            $this->updateCustomerData($currentCustomer, $customerData);
            $currentCustomer->last_active = now();
            $currentCustomer->save();

            return null; // No customer_id change needed
        }

        // Find or create new customer
        return $this->findOrCreateCustomer($customerData);
    }

    public function updateCustomerStats(Customer $customer): void
    {
        $customer->bookings_count = $customer->bookings()->count();
        $customer->last_active = now();
        $customer->save();
    }

    private function updateCustomerData(Customer $customer, array $data): void
    {
        $fields = ['first_name', 'last_name', 'phone', 'language'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $customer->$field = $data[$field];
            }
        }
    }
}
