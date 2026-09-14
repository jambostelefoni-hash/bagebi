import React from 'react';
import { Typography } from '@material-ui/core';

function CheckoutSuccess(props) {
  const isWaiting = props.responseData.application_status === 'waiting';
  const statusLabel = props.responseData.application_status_label || 'დარეგისტრირებული';
  return (
    <div className="registration-success">
      <div className={`registration-success-icon${isWaiting ? ' registration-success-icon--waiting' : ''}`}>{isWaiting ? '№' : '✓'}</div>
      <Typography variant="h5">{isWaiting ? 'განაცხადი მომლოდინეთა რიგშია' : 'განაცხადი წარმატებით დარეგისტრირდა'}</Typography>
      {isWaiting ? (
        <React.Fragment>
          <Typography variant="body2">არჩეულ ჯგუფში თავისუფალი ადგილი არ იყო, ამიტომ განაცხადი ავტომატურად დაემატა მომლოდინეთა სიას.</Typography>
          <div className="registration-queue-position"><span>თქვენი რიგითი ნომერი</span><strong>{props.responseData.queue_position || '—'}</strong></div>
        </React.Fragment>
      ) : (
        <div className="registration-result-status"><span>განაცხადის სტატუსი</span><strong>{statusLabel}</strong></div>
      )}
      <Typography variant="body2">შემდგომი ცვლილების შესახებ შეტყობინებას მითითებულ ელფოსტაზე მიიღებთ.</Typography>
      <a href="/">მთავარ გვერდზე დაბრუნება →</a>
    </div>
  );
}

export default CheckoutSuccess;

