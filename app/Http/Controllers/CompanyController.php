<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Response\BaseResponse;
use Illuminate\Http\Request;
use Log;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        if ($request->user()->hasRole('superadmin')) {
            $companies = Company::all();
        }else{
            $companies = Company::find($request->user()->company_id);
        }
        return BaseResponse::successData($companies->toArray(),'Data perusahaan berhasil diambil');
    }
    
    public function verification (Request $request){
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:diterima,ditolak',
        ]);

        $company = Company::find($validatedData['company_id']);
        $company->status = $validatedData['status'];
        $company->save();

        return BaseResponse::successMessage('Status perusahaan berhasil diperbarui');
    }

    public function update (Request $request, $id)
    {
        
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:companies,email,' . $id,
            'hex_color' => 'nullable|string',
            'link_default' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);
        
        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }
        
        $validatedData = $validator->validated();
        $company = Company::find($id);
        // Handle file upload for logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('logo', $filename, 'public');
            Log::info('Logo uploaded to: ' . $path);
            $validatedData['logo'] = $path;
        } 
        $company->update($validatedData);

        return BaseResponse::successData($company->toArray(), 'Company updated successfully');
    }

}
