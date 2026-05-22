<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'trainer_id' => $this->trainer_id,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'remaining_days' => $this->end_date ? now()->diffInDays($this->end_date, false) : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'membership_plan' => new MembershipPlanResource($this->whenLoaded('membershipPlan')),
            'trainer' => new TrainerProfileResource($this->whenLoaded('trainer')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
