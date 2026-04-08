package com.puneeventhub.bookingvalidator.service;

import com.puneeventhub.bookingvalidator.dto.BookingValidationRequest;
import com.puneeventhub.bookingvalidator.dto.BookingValidationResponse;
import com.puneeventhub.bookingvalidator.model.VenueSlot;
import com.puneeventhub.bookingvalidator.repository.VenueSlotRepository;
import java.time.LocalDateTime;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class BookingValidatorService {

    private final VenueSlotRepository venueSlotRepository;

    public BookingValidatorService(VenueSlotRepository venueSlotRepository) {
        this.venueSlotRepository = venueSlotRepository;
    }

    @Transactional
    public BookingValidationResponse validateAndHold(BookingValidationRequest request) {
        VenueSlot slot = venueSlotRepository.lockById(request.getSlotId())
                .orElseThrow(() -> new IllegalArgumentException("Slot not found"));

        LocalDateTime now = LocalDateTime.now();
        boolean activeHoldExists = slot.getHoldExpiresAt() != null && slot.getHoldExpiresAt().isAfter(now);

        if ("BOOKED".equalsIgnoreCase(slot.getStatus())) {
            return new BookingValidationResponse("REJECTED", "Slot is already booked.", request.getBookingReference(), slot.getHoldExpiresAt());
        }

        if (activeHoldExists && !request.getBookingReference().equals(slot.getHoldReference())) {
            return new BookingValidationResponse("REJECTED", "Slot is temporarily locked by another checkout session.", request.getBookingReference(), slot.getHoldExpiresAt());
        }

        LocalDateTime holdUntil = now.plusMinutes(10);
        slot.setStatus("HELD");
        slot.setHoldReference(request.getBookingReference());
        slot.setHoldExpiresAt(holdUntil);
        venueSlotRepository.save(slot);

        return new BookingValidationResponse("HELD", "Slot successfully locked for checkout.", request.getBookingReference(), holdUntil);
    }
}
