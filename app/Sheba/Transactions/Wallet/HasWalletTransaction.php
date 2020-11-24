<?php namespace Sheba\Transactions\Wallet;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasWalletTransaction
{
    /**
     * @return HasMany
     */
    public function transactions();

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @param array $options
     * @return bool|int
     */
    public function update(array $attributes = [], array $options = []);

    public function reload();
}
