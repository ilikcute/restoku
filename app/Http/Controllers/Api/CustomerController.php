<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Master\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Master\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\Master\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends BaseApiController
{
    public function index(Request $request)
    {
        $customers = Customer::where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->get();

        return $this->successResponse(CustomerResource::collection($customers));
    }

    public function store(StoreCustomerRequest $request)
    {
        Log::info('Customer Store Request Received', $request->all());
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $customer = Customer::create($validated);
        Log::info('Customer Created Successfully', ['id' => $customer->id]);

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
        $customer->update($request->validated());

        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeTenant($customer);
        $customer->delete();

        return $this->successResponse(null, 'Customer deleted successfully');
    }
}
