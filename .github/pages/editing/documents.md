---
title:  "Documents"
---

## Content

- Uploading a new document
- Verifying a document upload
- Linking to a document from a page
- Uploading a new version of a document
- Reporting an issue

## Uploading a document

To upload a new document, follow these steps (it's safe to click Update at any point to save your progress):

- Click Documents > Add Document in the Sidebar.
- Type a title for the document in the Title field.
- Click Upload New Version to select the document file from your computer.

### Optional document settings

- After uploading a file, you have the opportunity to add an optional Revision Summary for the document.
  This Revision Summary will be visible only to editors and will assist in tracking changes to the document.
- A Parent page may optionally be assigned to the Document. 
  This will assist in organizing the document within the site, and form part of the document's URL.
  e.g. If the parent is set to Civil Procedure Rules, the document will be accessible at `https://www.justice.gov.uk//courts/procedure-rules/civil/documents/document-title`.
  Set this on the right hand side: go to Document Attributes and use the Parent page dropdown.
- An optional Workflow State can be set to assist in tracking the document's progress.
  This is set on the right hand side: go to Document Attributes and use the Workflow State dropdown.

### Save & publish the document

- If you are ready to publish the document, set Visibility to Public and click OK.
- Click the Publish button.
- A permalink will be generated for the document. You can change it by clicking the Edit button next to the permalink.
  The permalink is the URL that will be used to access the document.
  It should only be set once and should not be changed after the document is published.
- Right click the permalink to either copy the link or open it in a new tab.
- This permalink can now be used to link to the document from other pages on the site.

### Schedule publication

A document can be scheduled for publication, by setting a published date in the future.

- Click the Edit button next to the Publish Date.
- Set the date and time you want the document to be published.
- Click OK.
- Click Schedule.

### Gotchas

- If you don't set the document's Visibility to Public, then the document will not be accessible to the public.
  This is an easy step to miss, as the default visibility is Private, and as a logged in editor you will be able to see the document regardless of its visibility setting.
- Setting a published date in the future is OK when creating a document, but be aware that it is not useful for scheduling a revision, because it will take all versions of the document offline until the scheduled date.

## Verifying a successful document upload

From the document edit page, you can verify that the document has been published successfully by checking the following:

- Right click on the permalink and selecting: Open link in new Incognito window.
  This will open the document in a new browser window where you are not logged in.
  If you can see the document, then it has been published successfully.
- The document should either display in the browser, or start downloading to your computer.

Another way to identify documents that have not been published is from the list view.

- On the Sidebar, click Documents > All Documents.
- At the top of the screen, you'll see a list of document statuses, 
  e.g. Published, Pending, Private... with the number of documents in each status.
  You can click these to show the documents with that status.
- Ensure that the number of documents with each status is as expected.

From the Documents view, we can also see the number of revisions and attachments for each document. 
This can be useful in identifying any documents where an upload was not completed, or failed.

## Linking to a document from a page

To link to a document from a page, follow these steps:

- Copy the Document's permalink, either:
  - from the document's edit page 
  - or, in the Documents view, hover the document and right-click on View, then click Copy link address.
- Go to the page you want to link from, and click Edit.
- Highlight the text you want to link from, and click the Link icon button - it's alongside the bold and italic buttons.
- Paste the permalink into the URL field.
- Press the ENTER key to add the link.
- Click Update to save your changes.

## Uploading a new version of a document

This process is similar to uploading a new document, but with a few differences:

- Click Documents > All Documents in the Sidebar.
- Find the document you want by searching and/or sorting.
  Alternatively, if you know the document's URL, you can append `/_admin` and it will take you to the edit page.
- Click Upload New Version to select the document file from your computer.
- Add a Revision Summary if necessary.
- Click Update.
- Verify the document has been published successfully, by following the steps in the Verify section.

At this stage you should avoid updating the permalink, as this will break any existing links to the document.

### Scheduling a revision

TODO

---

## Reporting an issue

To report an issue or to raise a support query, please contact the Central Digital Product Team at: justice-support@digital.justice.gov.uk
