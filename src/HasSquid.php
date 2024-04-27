<?php
namespace CronxCo\DataModel;
use CronxCo\DataModel\Sqid;
use Illuminate\Database\Eloquent\Casts\Attribute;
trait HasSqid
{
  /**
   * Get the obfuscated version of the model Id.
   *
   * @see https://sqids.org
   */
  protected function sqid(): Attribute
  {
    return Attribute::make(
      get: fn () => Sqid::encode($this->event_id)
    );
  }

  /**
   * Get the route key for the model.
   *
   * @return string
   */
  public function getRouteKeyName()
  {
    return 'sqid';
  }

  /**
 * Retrieve the model for a bound value.
 *
 * @param mixed $value
 * @param string|null $field
 * @return \Illuminate\Database\Eloquent\Model|null
 */
  public function resolveRouteBinding($value, $field = null)
  {
    return $this->resolveRouteBindingQuery($this, Sqid::decode($value), 'event_id')->first();
  }

}