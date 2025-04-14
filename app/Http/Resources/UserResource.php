<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'lang'          => $this->lang,
            'email'         => $this->email,
            'country'       => $this->getCountryISO($this->country),
            'phone'         => $this->phone,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'role'          => $this->role->name
        ];
    }
}
