package com.puneeventhub.bookingvalidator.repository;

import com.puneeventhub.bookingvalidator.model.VenueSlot;
import jakarta.persistence.LockModeType;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Lock;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface VenueSlotRepository extends JpaRepository<VenueSlot, Long> {

    @Lock(LockModeType.PESSIMISTIC_WRITE)
    @Query("select vs from VenueSlot vs where vs.id = :slotId")
    Optional<VenueSlot> lockById(@Param("slotId") Long slotId);
}
