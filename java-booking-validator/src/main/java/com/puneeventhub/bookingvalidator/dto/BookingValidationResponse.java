package com.puneeventhub.bookingvalidator.dto;

import java.time.LocalDateTime;

public record BookingValidationResponse(
        String status,
        String message,
        String bookingReference,
        LocalDateTime holdExpiresAt
) {
}
