<?php namespace App\Sheba\Resource\WithdrawalRequest;

use App\Models\Resource;
use Sheba\Dal\WithdrawalRequest\Statuses;

class WithdrawalRequestDenialMessage
{
    private $status;
    private $resource;

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getWithdrawalRequestDenialMessage()
    {
        $status = [];

        if (!$this->resource->is_verified) {
            $status['tag'] = 'not_verified';
            $status['message'] = 'আপনি বর্তমানে আনভেরফাইড অবস্থায় আছেন, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না বিস্তারিত জানতে ১৬৫১৬ নম্বরে যোগাযোগ করুন।';
        }
        elseif ($this->resource->totalWalletAmount() <= 0) {
            $status['tag'] = 'not_enough_balance';
            $status['message'] = 'আপনার অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স নেই, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।';
        }
        elseif ($this->status == Statuses::PENDING) {
            $status['tag'] = Statuses::PENDING;
            $status['message'] = 'আপনার একটি টাকা উত্তোলনের রিকুয়েস্ট এখনো অপেক্ষমাণ আছে।';
        }
        elseif ($this->status == Statuses::APPROVAL_PENDING) {
            $status['tag'] = Statuses::APPROVAL_PENDING;
            $status['message'] = 'আপনার সর্বশেষ ব্যালেন্স উত্তোলনের রিকুয়েস্ট এখনো অপেক্ষমাণ আছে, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।';
        }
        elseif ($this->status == Statuses::APPROVED) {
            $status['tag'] = Statuses::APPROVED;
            $status['message'] = 'আপনার সর্বশেষ ব্যালেন্স উত্তোলনের রিকুয়েস্ট এখনো অপেক্ষমাণ আছে, তাই আপনি এখন রিকুয়েস্ট করতে পারবেন না।';
        }
        elseif ($this->status == Statuses::REJECTED) {
            $status['tag'] = Statuses::REJECTED;
            $status['message'] = 'আপনার সর্বশেষ টাকা উত্তোলনের রিকুয়েস্টটি অনুমোদন করা হয়নি। বিস্তারিত জানতে ১৬৫১৬ নম্বরে কল দিন।';
        }
        elseif ($this->status == Statuses::FAILED) {
            $status['tag'] = Statuses::FAILED;
            $status['message'] = 'দুঃখিত! আপনার ব্যালেন্স উত্তোলনের রিকোয়েস্টটি সফল হয়নি।';
        }
        elseif ($this->status == Statuses::CANCELLED) {
            $status['tag'] = Statuses::CANCELLED;
            $status['message'] = 'আপনার সর্বশেষ টাকা উত্তোলনের রিকুয়েস্টটি বাতিল করা হয়েছে। বিস্তারিত জানতে ১৬৫১৬ নম্বরে কল দিন।';
        }
        else {
            $status['tag'] = null;
            $status['message'] = null;
        }

        return $status;
    }
}