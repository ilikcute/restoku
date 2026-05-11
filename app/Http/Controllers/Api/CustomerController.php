<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Master\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\Master\CustomerResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends BaseApiController
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    public function index(Request $request)
    {
        $customers = $this->customerRepository->getAllByTenant(
            $request->user()->tenant_id,
            $request->search,
            $request->integer('per_page') ?: null
        );

        return $this->successResponse(CustomerResource::collection($customers));
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $customer = $this->customerRepository->create($validated);

        return $this->successResponse(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    public function show(Customer $customer)
    {
        $this->authorizeTenant($customer);

        return $this->successResponse(new CustomerResource($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->authorizeTenant($customer);

        $customer = $this->customerRepository->update($customer->id, $request->validated());

        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeTenant($customer);

        $this->customerRepository->delete($customer->id);

        return $this->successResponse(null, 'Customer deleted successfully');
    }
}
