import React from 'react';
import { CssBaseline } from '@material-ui/core';
import { ThemeProvider } from '@material-ui/core/styles';
import { theme, useStyle } from './styles';

export default function MaterialLayout({ children }) {
  const classes = useStyle();
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <div className={classes.page}>{children}</div>
    </ThemeProvider>
  );
}
