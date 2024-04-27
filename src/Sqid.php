<?php
namespace CronxCo\DataModel;
use Sqids\Sqids;
final class Sqid
{
  /**
   * Encode an integer as a hashid.
   */
  public static function encode(?int $number): string
  {
    if (is_null($number)) {
      return '';
    }
    return resolve(Sqids::class)->encode([$number]);
  }
  /**
   * Decode a sqid string into an integer.
   */
  public static function decode(string $sqid): int
  {
    return resolve(Sqids::class)->decode($sqid)[0];
  }
}