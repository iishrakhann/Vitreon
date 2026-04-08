package com.puneeventhub.bookingvalidator.controller;

import com.puneeventhub.bookingvalidator.dto.BookingValidationRequest;
import com.puneeventhub.bookingvalidator.dto.BookingValidationResponse;
import com.puneeventhub.bookingvalidator.service.BookingValidatorService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/bookings")
public class BookingValidationController {

    private final BookingValidatorService bookingValidatorService;

    public BookingValidationController(BookingValidatorService bookingValidatorService) {
        this.bookingValidatorService = bookingValidatorService;
    }

    @PostMapping("/validate")
    public ResponseEntity<BookingValidationResponse> validate(@Valid @RequestBody BookingValidationRequest request) {
        return ResponseEntity.ok(bookingValidatorService.validateAndHold(request));
    }
}
