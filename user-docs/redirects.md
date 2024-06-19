# Redirects

Due to the dynamic nature of the content on this website, it may be necessary to change or delete a page or document. When this happens, it is important to create a redirect to ensure that users are not met with a 404 error when trying to access the old URL.

## Creating a redirect

To create a redirect, follow these steps:

- Click Tools > Redirect Manager in the Sidebar.
- Click Create Redirect Rule
- In the redirect from field, enter the old URL that you want to redirect from.
  It should be the part of the url after `https://www.justice.gov.uk` e.g. `/my-old-page`.
- In the redirect to field, enter the new URL that you want to redirect to.
  If the new URL is on this site, you can type the part of the url after `https://www.justice.gov.uk` e.g. `/my-new-page`.
- Leave the Status Code as 301 Move Permanently, unless you have a specific reason to change it.
- Optionally add a Note to describe the reason for the redirect.
- Leave the Post Attributes > Order field as 0.
- Click Publish.
- Verify the redirect by visiting the old URL in a new browser window.

## Gotchas

- Be cautions not to create 2 redirects that direct to each other. This will create an loop and cause the page to fail to load.
- When updating a redirect, be aware that if you change the 'Redirect From' URL, this may result in a 404 error error for the old URL.
- Similarly, deleting a redirect rule could cause a 404 error for the old URL. It is usually best not to delete a redirect rule, but to update it to point to a new URL instead.

---

## Reporting an issue

To report an issue or to raise a support query, please contact the Central Digital Product Team at: justice-support@digital.justice.gov.uk
