<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\WhatsAppContact;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * Maat support WhatsApp contact (`GET /waffarha/whatsapp`).
 *
 * Phone number is sourced server-side from `tbl_setting.app_phone_number`.
 */
final class WhatsApp extends Resource
{
    /**
     * Fetch Maat's WhatsApp support contact + deep links.
     *
     * @throws WaffarhaRequestException
     */
    public function get(): WhatsAppContact
    {
        return WhatsAppContact::fromArray(
            $this->transport->send('GET', 'whatsapp')
        );
    }
}
