<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\WhatsAppContact;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * Maat support WhatsApp (`GET /waffarha/whatsapp`).
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
