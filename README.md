# paymentcontributionsync

For historical reasons, CiviCRM stores "Check Number" and "Payment Method" on the Contribution, rather than the Payment.  This means you can't search by check number if it wasn't entered when the contribution was made (e.g. with a "Pay Later" payment).  Exporting contributions also gives the incorrect values.

While plans for a [comprehensive solution](https://lab.civicrm.org/dev/financial/-/issues/37) exist, they are ambitious and don't have momentum.  This extension fills that need until such time as the database structure is revamped.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* Tested on CiviCRM 5.32+.  Should work on much older versions though.

## Usage

There are no settings.  You can test that it's working by recording a contribution, then updating its payment to set a new payment method.  When you view the contribution, the "payment method" will be correct with this extension, and show the original payment method otherwise.

## Known Issues

This extension ignores any contribution that has partial payments; the "correct" value is subject to debate there.

This extension uses raw SQL to bypass hooks.  This is dangerous; please evaluate it on a test site for yourself.
